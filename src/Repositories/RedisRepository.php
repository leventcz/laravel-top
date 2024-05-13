<?php

declare(strict_types=1);

namespace Leventcz\Top\Repositories;

use Illuminate\Contracts\Redis\Factory as RedisFactory;
use Illuminate\Redis\Connections\Connection;
use Leventcz\Top\Contracts\Repository;
use Leventcz\Top\Data\DatabaseSummary;
use Leventcz\Top\Data\EventCounter;
use Leventcz\Top\Data\HandledRequest;
use Leventcz\Top\Data\RequestSummary;
use Leventcz\Top\Data\RouteCollection;

readonly class RedisRepository implements Repository
{
    public function __construct(
        private RedisFactory $factory
    ) {
    }

    public function save(HandledRequest $request, EventCounter $eventCounter): void
    {
        $this->connection()->pipeline(function ($pipe) use ($request, $eventCounter) {
            $key = "top-requests:$request->timestamp";
            $routeKey = $request->route.':data';

            foreach ($eventCounter->get() as $event => $times) {
                $pipe->hIncrBy($key, "$routeKey:$event", $times);
            }

            $pipe->hIncrBy($key, $routeKey.':hits', 1);
            $pipe->hIncrBy($key, $routeKey.':memory', $request->memory);
            $pipe->hIncrBy($key, $routeKey.':duration', $request->duration);
            $pipe->expire($key, 600);
        });
    }

    public function getRequestSummary(): RequestSummary
    {
        $keys = $this->buildKeys(now()->getTimestamp());

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
                    elseif metric == 'memory' then
                        totalMemory = totalMemory + value
                    elseif metric == 'duration' then
                        totalDuration = totalDuration + value
                    end
                end
            end

            local averageRequestPerSecond = totalRequests / 5
            local averageMemoryUsage = (totalRequests > 0 and totalMemory / totalRequests) or 0
            local averageDuration = (totalRequests > 0 and totalDuration / totalRequests) or 0

            return cjson.encode({averageRequestPerSecond = averageRequestPerSecond, averageMemoryUsage = averageMemoryUsage, averageDuration = averageDuration})
        LUA;

        $result = $this->connection()->eval($script, count($keys), ...$keys);

        return RequestSummary::fromArray(json_decode($result, true));
    }

    public function getDatabaseSummary(): DatabaseSummary
    {
        $keys = $this->buildKeys(now()->getTimestamp());

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
                    elseif metric == 'database-query-executed' then
                        totalQueryExecuted = totalQueryExecuted + value
                    elseif metric == 'database-query-execution-time' then
                        totalQueryDuration = totalQueryDuration + value
                    end
                end
            end

            local averageQueryPerSecond = totalQueryExecuted / 5
            local averageQueryDuration = (totalRequests > 0 and totalQueryDuration / totalRequests) or 0

            return cjson.encode({averageQueryPerSecond = averageQueryPerSecond, averageQueryDuration = averageQueryDuration})
        LUA;

        $result = $this->connection()->eval($script, count($keys), ...$keys);

        return DatabaseSummary::fromArray(json_decode($result, true));
    }

    public function getTopRoutes(): RouteCollection
    {
        $keys = $this->buildKeys(now()->getTimestamp());

        $script = <<<'LUA'
            local keys = KEYS
            local routeCounts = {}

            for _, key in ipairs(keys) do
                local fields = redis.call('HGETALL', key)
                for i = 1, #fields, 2 do
                    local field = fields[i]
                    local value = tonumber(fields[i + 1])
                    local route, metric = field:match("([^:]+):data:([^:]+)")
                    if not routeCounts[route] then
                        routeCounts[route] = {hits = 0, memory = 0, duration = 0}
                    end
                    if metric == 'hits' then
                        routeCounts[route].hits = routeCounts[route].hits + value
                    elseif metric == 'memory' then
                        routeCounts[route].memory = routeCounts[route].memory + value
                    elseif metric == 'duration' then
                        routeCounts[route].duration = routeCounts[route].duration + value
                    end
                end
            end

            local topRoutes = {}
            for route, counts in pairs(routeCounts) do
                local averageRequestPerSecond = counts.hits / 5
                local averageMemoryUsage = (counts.hits > 0 and counts.memory / counts.hits) or 0
                local averageDuration = (counts.hits > 0 and counts.duration / counts.hits) or 0
                table.insert(topRoutes, {route = route, averageRequestPerSecond = averageRequestPerSecond, averageMemoryUsage = averageMemoryUsage, averageDuration = averageDuration})
            end

            table.sort(topRoutes, function(a, b) return a[2] > b[2] end)
            topRoutes = #topRoutes > 5 and {unpack(topRoutes, 1, 5)} or topRoutes

            return cjson.encode(topRoutes)
        LUA;

        $result = $this->connection()->eval($script, count($keys), ...$keys);

        return RouteCollection::collect(json_decode($result, true));
    }

    private function buildKeys(int $timestamp): array
    {
        $keys = [];
        for ($i = 0; $i < 5; $i++) {
            $keys[] = 'top-requests:'.($timestamp - $i);
        }

        return $keys;
    }

    private function connection(): Connection
    {
        return $this->factory->connection();
    }
}
