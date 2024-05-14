<p align="center"><img src="/art/top.gif" alt="Real-time monitoring with Laravel Top"></p>

```php
php artisan top
```
**Top** provides real-time monitoring directly from the command line for Laravel applications. It is designed for production environments, enabling you to effortlessly track essential metrics and identify the busiest routes.

## How it works?

**Top** listens to Laravel events and saves aggregated data to Redis hashes behind the scenes to calculate metrics. The aggregated data is stored with a short TTL, ensuring that historical data is not retained and preventing Redis from becoming overloaded. During display, metrics are calculated based on the average of the last 5 seconds of data.

## Installation

> **Requires [PHP 8.2+](https://php.net/releases/) and [Redis 5.0+](https://redis.io)**

```bash
composer require leventcz/laravel-top
```

## Configuration

By default, **Top** uses the default Redis connection. To change the connection, you need to edit the configuration file.

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
