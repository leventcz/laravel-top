<?php

declare(strict_types=1);

namespace Leventcz\Top\Data;

class HandledRequest extends Data
{
    public function __construct(
        public string $uri,
        public string $method,
        public int $timestamp,
        public int $memory,
        public int $duration,
    ) {
    }
}
