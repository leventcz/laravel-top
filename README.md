<p align="center"><img src="/art/top.gif" alt="Real-time monitoring with Laravel Top"></p>

```php
php artisan top
```
**Top** provides real-time monitoring directly from the command line for Laravel applications. 
It is designed for use in production environments, allowing you to track essential metrics and the busiest routes with ease.

## Installation via Composer

> **Requires [PHP 8.2+](https://php.net/releases/) and [Redis 5.0+](https://redis.io)**

```bash
composer require leventcz/laravel-top
```

## Configuration

By default, **Top** uses the default Redis connection. If you want to change connection, you need to edit config file.

You can publish the config file with:

```bash
php artisan vendor:publish --tag="top"
```

```php
<?php

return [
    /*
     * Provide a redis connection from config/database.php
    */
    'connection' => env('TOP_REDIS_CONNECTION', 'default')
];

```

## Facade

If you want to access metrics in your application, you can use the **Top** facade.

```php
<?php

use Leventcz\Top\Facades\Top;
use Leventcz\Top\Data\Route;

$requestSummary = Top::http();
$requestSummary->averageRequestPerSecond;
$requestSummary->averageMemoryUsage;
$requestSummary->averageDuration;

$databaseSummary = Top::database();
$databaseSummary->averageQueryPerSecond;
$databaseSummary->averageQueryDuration;

$cacheSummary = Top::cache();
$cacheSummary->averageHitPerSecond;
$cacheSummary->averageMissPerSecond;
$cacheSummary->averageWritePerSecond;

$topRoutes = Top::routes();
$topRoutes->each(function(Route $route) {
    $route->uri;
    $route->method;
    $route->averageRequestPerSecond;
    $route->averageMemoryUsage;
    $route->averageDuration;
})
```
## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Testing

```bash
composer test
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
