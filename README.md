
```php
php artisan top
```
**Top** provides real-time monitoring directly from the command line for Laravel applications. It allows you to track essential metrics and the busiest routes with ease.

## Installation via Composer

> **Requires [PHP 8.2+](https://php.net/releases/) and [Redis 5.0+](https://redis.io)**

```bash
composer require leventcz/laravel-top
```

## Configuration

By default, **Top** uses the default Redis connection. If you wish to change connection, you need to edit config file.

You can publish the config file with:

```bash
php artisan vendor:publish --tag="top"
```

```php
<?php

return [
    'connection' => env('TOP_REDIS_CONNECTION', 'default')
];

```

## Testing

```bash
composer test
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
