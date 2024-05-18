<?php

use Illuminate\Config\Repository;
use Illuminate\Contracts\Redis\Factory;
use Illuminate\Foundation\Application;
use Illuminate\Redis\RedisManager;
use Leventcz\Top\Facades\State;
use Leventcz\Top\Facades\Top;
use Leventcz\Top\ServiceProvider;
use Leventcz\Top\StateManager;
use Leventcz\Top\TopManager;

beforeEach(function () {
    $this->app = new Application();
    $this->app->bind('config', fn() => new Repository());
    $this->app->bind(Factory::class, fn() => Mockery::mock(RedisManager::class));
    $this->app->register(ServiceProvider::class);
});

afterAll(function () {
    Mockery::close();
});

it('ensures top is bound to the container', function () {
    expect($this->app->get('top'))
        ->toBeInstanceOf(TopManager::class);
});

it('ensures top.state bound to the container', function () {
    expect($this->app->get('top.state'))
        ->toBeInstanceOf(StateManager::class);
});

it('ensures top is always singleton', function () {
    $top = $this->app->get('top');

    expect($this->app->get('top'))
        ->toBe($top);
});

it('ensures top facade resolves correctly', function () {
    Top::setFacadeApplication($this->app);

    expect(Top::getFacadeRoot())
        ->toBeInstanceOf(TopManager::class);
});

it('ensures state facade resolves correctly', function () {
    State::setFacadeApplication($this->app);

    expect(State::getFacadeRoot())
        ->toBeInstanceOf(StateManager::class);
});
