<?php

declare(strict_types=1);

namespace Leventcz\Top\Facades;

use Illuminate\Support\Facades\Facade;
use Leventcz\Top\Data\HandledRequest;
use Leventcz\Top\StateManager;

/**
 * @method static StateManager add(string $event, int $times = 1)
 * @method static void save(HandledRequest $request)
 * @method static static flush()
 */
class State extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'top.state';
    }
}
