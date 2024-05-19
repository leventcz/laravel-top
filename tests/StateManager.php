<?php

use Leventcz\Top\Contracts\Repository;
use Leventcz\Top\Data\EventCounter;
use Leventcz\Top\Data\HandledRequest;
use Leventcz\Top\StateManager;

beforeEach(function () {
    $this->eventCounter = Mockery::mock(EventCounter::class);
    $this->repository = Mockery::mock(Repository::class);
    $this->stateManager = new StateManager($this->eventCounter, $this->repository);
});

afterAll(function () {
    Mockery::close();
});

it('adds event to state', function () {
    $this
        ->eventCounter
        ->shouldReceive('add')
        ->with('test', 4)
        ->once();

    $this->stateManager->add('test', 4);
});

it('flushes state', function () {
    $this
        ->eventCounter
        ->shouldReceive('flush')
        ->once();

    $this->stateManager->flush();
});

it('saves state', function () {
    $request = new HandledRequest('', '', 1, 1, 1);

    $this
        ->repository
        ->shouldReceive('save')
        ->with($request, $this->eventCounter)
        ->once();

    $this->stateManager->save($request);
});
