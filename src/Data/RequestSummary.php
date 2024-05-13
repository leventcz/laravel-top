<?php

declare(strict_types=1);

namespace Leventcz\Top\Data;

class RequestSummary extends Data
{
    public function __construct(
        public float $averageRequestPerSecond,
        public float $averageMemoryUsage,
        public float $averageDuration,
    ) {
    }
}
