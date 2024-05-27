<?php

declare(strict_types=1);

namespace Leventcz\Top;

use Illuminate\Contracts\Redis\Factory;
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
        $this->mergeConfigFrom(__DIR__.'/../config/top.php', 'top');
        $this->app->singleton('top', TopManager::class);
        $this->app->singleton(Repository::class, function (Application $application) {
            $connection = $application
                ->make(Factory::class)
                ->connection($application['config']->get('top.connection'));

            return new RedisRepository($connection);
        });
        $this->app->bind('top.state', function (Application $application) {
            return new StateManager(
                $application->make(EventCounter::class),
                $application->make(Repository::class)
            );
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([TopCommand::class]);
            $this->publishes([__DIR__.'/../config/top.php' => config_path('top.php')], 'top');

            return;
        }

        $this->app->make('events')->subscribe(RequestListener::class);
        $this->app->make('events')->subscribe(CacheListener::class);
        $this->app->make('events')->subscribe(DatabaseListener::class);
    }
}
