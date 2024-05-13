<?php

declare(strict_types=1);

namespace Leventcz\Top\Data;

class Route extends Data
{
    public function __construct(
        public string $route,
        public float $averageRequestPerSecond,
        public float $averageMemoryUsage,
        public float $averageDuration,
    ) {
    }

    public static function fromArray($attributes): Route
    {
        return new self(...$attributes);
    }
}
