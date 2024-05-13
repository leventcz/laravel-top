<?php

declare(strict_types=1);

namespace Leventcz\Top\Data;

class CacheSummary extends Data
{
    public function __construct(
        public float $averageHitPerSecond,
        public float $averageMissPerSecond,
        public float $averageWritePerSecond,
    ) {
    }
}
