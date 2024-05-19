<?php

use Illuminate\Config\Repository;
use Illuminate\Contracts\Redis\Factory as RedisFactory;
use Illuminate\Redis\Connections\Connection;
use Leventcz\Top\Data\CacheSummary;
use Leventcz\Top\Data\DatabaseSummary;
use Leventcz\Top\Data\EventCounter;
use Leventcz\Top\Data\HandledRequest;
use Leventcz\Top\Data\RequestSummary;
use Leventcz\Top\Data\RouteCollection;
use Leventcz\Top\Repositories\RedisRepository;

beforeEach(function () {
    $this->redisFactory = Mockery::mock(RedisFactory::class);
    $this->connection = Mockery::mock(Connection::class);
    $this->config = Mockery::mock(Repository::class);

    $this->redisFactory
        ->shouldReceive('connection')
        ->andReturn($this->connection);
    $this->config
        ->shouldReceive('get')
        ->andReturn('top.connection');

    $this->repository = new RedisRepository($this->redisFactory, $this->config);
});

afterEach(function () {
    Mockery::close();
});

it('saves handled request and events to redis', function () {
    $request = new HandledRequest('GET', '/test', 100, 200, 1622505600);
    $eventCounter = new EventCounter(['some-event' => 5, 'another-event' => 10]);
    $pipe = Mockery::spy();

    $this
        ->connection
        ->shouldReceive('pipeline')
        ->once()
        ->with(Mockery::on(function ($callback) use ($pipe) {
            $callback($pipe);
            return true;
        }))
        ->andReturnNull();

    $this->repository->save($request, $eventCounter);

    $key = "top-requests:$request->timestamp";
    $routeKey = "$request->method:$request->uri:data";

    foreach ($eventCounter->get() as $event => $times) {
        $pipe
            ->shouldHaveReceived('hIncrBy')
            ->with($key, "$routeKey:$event", $times)
            ->once();
    }

    $pipe
        ->shouldHaveReceived('hIncrBy')
        ->with($key, "$routeKey:hits", 1)
        ->once();

    $pipe
        ->shouldHaveReceived('hIncrBy')
        ->with($key, "$routeKey:memory", $request->memory)
        ->once();

    $pipe
        ->shouldHaveReceived('hIncrBy')
        ->with($key, "$routeKey:duration", $request->duration)
        ->once();

    $pipe
        ->shouldHaveReceived('expire')
        ->with($key, 10)
        ->once();
});

it('fetches request summary from redis', function () {
    $this
        ->connection
        ->shouldReceive('eval')
        ->once()
        ->andReturn(json_encode(['averageRequestPerSecond' => 1, 'averageMemoryUsage' => 2, 'averageDuration' => 3]));

    expect($this->repository->getRequestSummary())
        ->toBeInstanceOf(RequestSummary::class);
});

it('fetches database summary from redis', function () {
    $this
        ->connection
        ->shouldReceive('eval')
        ->once()
        ->andReturn(json_encode(['averageQueryPerSecond' => 1, 'averageQueryDuration' => 2]));

    expect($this->repository->getDatabaseSummary())
        ->toBeInstanceOf(DatabaseSummary::class);
});

it('fetches cache summary from redis', function () {
    $this
        ->connection
        ->shouldReceive('eval')
        ->once()
        ->andReturn(json_encode([
            'averageHitPerSecond' => 1, 'averageMissPerSecond' => 2, 'averageWritePerSecond' => 3
        ]));

    expect($this->repository->getCacheSummary())
        ->toBeInstanceOf(CacheSummary::class);
});

it('fetches top routes from redis', function () {
    $this
        ->connection
        ->shouldReceive('eval')
        ->once()
        ->andReturn(json_encode([
            [
                'uri' => '', 'method' => '', 'averageRequestPerSecond' => 1, 'averageMemoryUsage' => 1,
                'averageDuration' => 1
            ]
        ]));

    expect($this->repository->getTopRoutes())
        ->toBeInstanceOf(RouteCollection::class);
});
