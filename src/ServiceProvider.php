<?php

declare(strict_types=1);

namespace Leventcz\Top;

use Illuminate\Events\Dispatcher;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Leventcz\Top\Commands\TopCommand;
use Leventcz\Top\Contracts\Repository;
use Leventcz\Top\Data\EventCounter;
use Leventcz\Top\Listeners\CacheListener;
use Leventcz\Top\Listeners\DatabaseListener;
use Leventcz\Top\Listeners\RequestListener;
use Leventcz\Top\Repositories\RedisRepository;

class ServiceProvider extends BaseServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Repository::class, RedisRepository::class);
        $this->app->bind('top.state', function (Application $app) {
            return new StateManager($app->make(EventCounter::class), $app->make(Repository::class));
        });
        $this->app->bind('top', TopManager::class);
    }

    public function boot(Dispatcher $dispatcher): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                TopCommand::class,
            ]);

            return;
        }

        $dispatcher->subscribe(RequestListener::class);
        $dispatcher->subscribe(CacheListener::class);
        $dispatcher->subscribe(DatabaseListener::class);
    }
}
