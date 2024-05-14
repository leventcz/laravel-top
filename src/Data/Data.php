<?php

declare(strict_types=1);

namespace Leventcz\Top\Data;

abstract class Data
{
    public static function fromArray(array $attributes): static
    {
        // @phpstan-ignore-next-line
        return new static(...$attributes);
    }
}
