<?php

declare(strict_types=1);

namespace Leventcz\Top\Data;

class Route extends Data
{
    public function __construct(
        public string $uri,
        public string $method,
        public float $averageRequestPerSecond,
        public float $averageMemoryUsage,
        public float $averageDuration,
    ) {
    }
}
