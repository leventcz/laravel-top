<?php

declare(strict_types=1);

namespace Leventcz\Top\Data;

class EventCounter extends Data
{
    public function __construct(
        private array $events = [],
    ) {
    }

    public function add(string $event, int $times = 1): static
    {
        $this->events[$event] = ($this->events[$event] ?? 0) + $times;

        return $this;
    }

    public function flush(): static
    {
        $this->events = [];

        return $this;
    }

    public function get(): array
    {
        return $this->events;
    }
}
