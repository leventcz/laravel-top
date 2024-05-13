<?php

declare(strict_types=1);

namespace Leventcz\Top\Listeners;

use Illuminate\Cache\Events\CacheHit;
use Illuminate\Cache\Events\CacheMissed;
use Illuminate\Cache\Events\KeyWritten;
use Leventcz\Top\Facades\State;

class CacheListener
{
    public function cacheHit(): void
    {
        State::add('cache-hit');
    }

    public function cacheMissed(): void
    {
        State::add('cache-missed');
    }

    public function keyWritten(): void
    {
        State::add('cache-written');
    }

    public function subscribe(): array
    {
        return [
            CacheHit::class => 'cacheHit',
            CacheMissed::class => 'cacheMissed',
            KeyWritten::class => 'keyWritten',
        ];
    }
}
