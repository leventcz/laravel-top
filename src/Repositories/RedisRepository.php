<?php

declare(strict_types=1);

namespace Leventcz\Top\Repositories;

use Illuminate\Redis\Connections\Connection;
use Leventcz\Top\Contracts\Repository;
use Leventcz\Top\Data\CacheSummary;
use Leventcz\Top\Data\DatabaseSummary;
use Leventcz\Top\Data\EventCounter;
use Leventcz\Top\Data\HandledRequest;
use Leventcz\Top\Data\RequestSummary;
use Leventcz\Top\Data\RouteCollection;

readonly class RedisRepository implements Repository
{
    private const TOP_STATUS_KEY = 'top-status';

    public function __construct(
        private Connection $connection
    ) {
    }

    public function save(HandledRequest $request, EventCounter $eventCounter): void
    {
        // @phpstan-ignore-next-line
        $this
            ->connection
            ->pipeline(function ($pipe) use ($request, $eventCounter) {
                $key = "top-requests:$request->timestamp";
                $routeKey = "$request->method:$request->uri:data";

                foreach ($eventCounter->get() as $event => $times) {
                    $pipe->hIncrBy($key, "$routeKey:$event", $times);
                }

                $pipe->hIncrBy($key, "$routeKey:hits", 1);
                $pipe->hIncrBy($key, "$routeKey:memory", $request->memory);
                $pipe->hIncrBy($key, "$routeKey:duration", $request->duration);
                $pipe->expire($key, 10);
            });
    }

    public function getRequestSummary(): RequestSummary
    {
        $script = <<<'LUA'
            local keys = KEYS
            local totalRequests = 0
            local totalMemory = 0
            local totalDuration = 0

            for _, key in ipairs(keys) do
                local fields = redis.call('HGETALL', key)
                for i = 1, #fields, 2 do
                    local field = fields[i]
                    local value = tonumber(fields[i + 1])
                    local _, metric = field:match("([^:]+):data:([^:]+)")
                    if metric == 'hits' then
                        totalRequests = totalRequests + value
                    end
                    if metric == 'memory' then
                        totalMemory = totalMemory + value
                    end
                    if metric == 'duration' then
                        totalDuration = totalDuration + value
                    end
                end
            end

            local averageRequestPerSecond = (totalRequests > 0 and totalRequests / 5) or 0
            local averageMemoryUsage = (totalRequests > 0 and totalMemory / totalRequests) or 0
            local averageDuration = (totalRequests > 0 and totalDuration / totalRequests) or 0

            return cjson.encode({
                averageRequestPerSecond = averageRequestPerSecond,
                averageMemoryUsage = averageMemoryUsage,
                averageDuration = averageDuration
            })
        LUA;

        return RequestSummary::fromArray($this->execute($script));
    }

    public function getDatabaseSummary(): DatabaseSummary
    {
        $script = <<<'LUA'
            local keys = KEYS
            local totalRequests = 0
            local totalQueryExecuted = 0
            local totalQueryDuration = 0

            for _, key in ipairs(keys) do
                local fields = redis.call('HGETALL', key)
                for i = 1, #fields, 2 do
                    local field = fields[i]
                    local value = tonumber(fields[i + 1])
                    local _, metric = field:match("([^:]+):data:([^:]+)")
                    if metric == 'hits' then
                        totalRequests = totalRequests + value
                    end
                    if metric == 'database-query-executed' then
                        totalQueryExecuted = totalQueryExecuted + value
                    end
                    if metric == 'database-query-execution-time' then
                        totalQueryDuration = totalQueryDuration + value
                    end
                end
            end

            local averageQueryPerSecond = (totalQueryExecuted > 0 and totalQueryExecuted / 5) or 0
            local averageQueryDuration = (totalRequests > 0 and totalQueryDuration / totalRequests) or 0

            return cjson.encode({
                averageQueryPerSecond = averageQueryPerSecond,
                averageQueryDuration = averageQueryDuration
            })
        LUA;

        return DatabaseSummary::fromArray($this->execute($script));
    }

    public function getCacheSummary(): CacheSummary
    {
        $script = <<<'LUA'
            local keys = KEYS
            local totalCacheHit = 0
            local totalCacheMissed = 0
            local totalCacheWritten = 0

            for _, key in ipairs(keys) do
                local fields = redis.call('HGETALL', key)
                for i = 1, #fields, 2 do
                    local field = fields[i]
                    local value = tonumber(fields[i + 1])
                    local _, metric = field:match("([^:]+):data:([^:]+)")
                    if metric == 'cache-hit' then
                        totalCacheHit = totalCacheHit + value
                    end
                    if metric == 'cache-missed' then
                        totalCacheMissed = totalCacheMissed + value
                    end
                    if metric == 'cache-written' then
                        totalCacheWritten = totalCacheWritten + value
                    end
                end
            end

            local averageHitPerSecond = (totalCacheHit > 0 and totalCacheHit / 5) or 0
            local averageMissPerSecond = (totalCacheMissed > 0 and totalCacheMissed / 5) or 0
            local averageWritePerSecond = (totalCacheWritten > 0 and totalCacheWritten / 5) or 0

            return cjson.encode({
                averageHitPerSecond = averageHitPerSecond,
                averageMissPerSecond = averageMissPerSecond,
                averageWritePerSecond = averageWritePerSecond
            })
        LUA;

        return CacheSummary::fromArray($this->execute($script));
    }

    public function getTopRoutes(): RouteCollection
    {
        $script = <<<'LUA'
            local keys = KEYS
            local uriCounts = {}

            for _, key in ipairs(keys) do
                local fields = redis.call('HGETALL', key)
                for i = 1, #fields, 2 do
                    local field = fields[i]
                    local value = tonumber(fields[i + 1])
                    local method, uri, metric = field:match("([^:]+):([^:]+):data:([^:]+)")
                    if method and uri and metric then
                        local uriMethod = method .. ":" .. uri
                        if not uriCounts[uriMethod] then
                            uriCounts[uriMethod] = {method = method, uri = uri, hits = 0, memory = 0, duration = 0}
                        end
                        if metric == 'hits' then
                            uriCounts[uriMethod].hits = uriCounts[uriMethod].hits + value
                        end
                        if metric == 'memory' then
                            uriCounts[uriMethod].memory = uriCounts[uriMethod].memory + value
                        end
                        if metric == 'duration' then
                            uriCounts[uriMethod].duration = uriCounts[uriMethod].duration + value
                        end
                    end
                end
            end

            local topRoutes = {}
            for uriMethod, counts in pairs(uriCounts) do
                local averageRequestPerSecond = counts.hits / 5
                local averageMemoryUsage = (counts.hits > 0 and counts.memory / counts.hits) or 0
                local averageDuration = (counts.hits > 0 and counts.duration / counts.hits) or 0
                table.insert(topRoutes, {uri = counts.uri, method = counts.method, averageRequestPerSecond = averageRequestPerSecond, averageMemoryUsage = averageMemoryUsage, averageDuration = averageDuration})
            end

            table.sort(topRoutes, function(a, b) return a.averageRequestPerSecond > b.averageRequestPerSecond end)
            topRoutes = #topRoutes > 20 and {unpack(topRoutes, 1, 20)} or topRoutes

            return cjson.encode(topRoutes)
        LUA;

        return RouteCollection::fromArray($this->execute($script));
    }

    public function recorderExists(): bool
    {
        return $this->connection->exists(self::TOP_STATUS_KEY) === 1; // @phpstan-ignore-line
    }

    public function setRecorder(int $duration = 5): void
    {
        $this->connection->setex(self::TOP_STATUS_KEY, $duration, true); // @phpstan-ignore-line
    }

    public function deleteRecorder(): void
    {
        $this->connection->del(self::TOP_STATUS_KEY); // @phpstan-ignore-line
    }

    private function execute(string $script): array
    {
        $keys = $this->generateKeys(now()->getTimestamp());
        $result = $this->connection->eval($script, count($keys), ...$keys); // @phpstan-ignore-line

        return json_decode($result, true);
    }

    private function generateKeys(int $timestamp): array
    {
        $keys = [];
        for ($i = 0; $i < 5; $i++) {
            $keys[] = 'top-requests:'.($timestamp - $i);
        }

        return $keys;
    }
}
