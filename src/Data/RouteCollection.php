<?php

declare(strict_types=1);

namespace Leventcz\Top\Data;

use Illuminate\Support\Collection;

class RouteCollection extends Data
{
    public function __construct(
        public Collection $items,
    ) {
    }

    public static function collect($attributes): RouteCollection
    {
        $items = collect();
        foreach ($attributes as $attribute) {
            $items->add(Route::fromArray($attribute));
        }

        return new self($items);
    }
}
