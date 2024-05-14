<?php

declare(strict_types=1);

namespace Leventcz\Top\Data;

use Illuminate\Support\Collection;

class RouteCollection extends Collection
{
    public static function fromArray(array $attributes): RouteCollection
    {
        $items = collect();
        foreach ($attributes as $attribute) {
            $items->add(Route::fromArray($attribute));
        }

        return new self($items);
    }
}
