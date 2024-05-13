<?php

declare(strict_types=1);

namespace Leventcz\Top\Data;

class DatabaseSummary extends Data
{
    public function __construct(
        public float $averageQueryPerSecond,
        public float $averageQueryDuration,
    ) {
    }

    public static function fromArray($attributes): DatabaseSummary
    {
        return new self(...$attributes);
    }
}
