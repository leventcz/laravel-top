<?php

use Leventcz\Top\Contracts\Repository;
use Leventcz\Top\Data\CacheSummary;
use Leventcz\Top\Data\DatabaseSummary;
use Leventcz\Top\Data\RequestSummary;
use Leventcz\Top\Data\RouteCollection;
use Leventcz\Top\TopManager;

beforeEach(function () {
    $this->repository = Mockery::mock(Repository::class);
    $this->topManager = new TopManager($this->repository);
});

afterAll(function () {
    Mockery::close();
});

it('returns http summary', function () {
    $expectedResult = new RequestSummary(1, 1, 1);

    $this
        ->repository
        ->shouldReceive('getRequestSummary')
        ->once()
        ->andReturns($expectedResult);

    expect($this->topManager->http())
        ->toBe($expectedResult);
});

it('returns database summary', function () {
    $expectedResult = new DatabaseSummary(1, 1);

    $this
        ->repository
        ->shouldReceive('getDatabaseSummary')
        ->once()
        ->andReturns($expectedResult);

    expect($this->topManager->database())
        ->toBe($expectedResult);
});

it('returns cache summary', function () {
    $expectedResult = new CacheSummary(1, 1, 1);

    $this
        ->repository
        ->shouldReceive('getCacheSummary')
        ->once()
        ->andReturns($expectedResult);

    expect($this->topManager->cache())
        ->toBe($expectedResult);
});

it('returns top routes', function () {
    $expectedResult = new RouteCollection();

    $this
        ->repository
        ->shouldReceive('getTopRoutes')
        ->once()
        ->andReturns($expectedResult);

    expect($this->topManager->routes())
        ->toBe($expectedResult);
});
