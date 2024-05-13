<?php

declare(strict_types=1);

namespace Leventcz\Top\Data;

abstract class Data
{
    public static function fromArray($attributes): static
    {
        return new static(...$attributes);
    }
}
