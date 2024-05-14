<?php

declare(strict_types=1);

namespace Leventcz\Top;

use Leventcz\Top\Contracts\Repository;
use Leventcz\Top\Data\EventCounter;
use Leventcz\Top\Data\HandledRequest;

readonly class StateManager
{
    public function __construct(
        private EventCounter $eventCounter,
        private Repository $repository
    ) {
    }

    public function add(string $event, int $times = 1): static
    {
        $this->eventCounter->add($event, $times);

        return $this;
    }

    public function flush(): static
    {
        $this->eventCounter->flush();

        return $this;
    }

    public function save(HandledRequest $handledRequest): void
    {
        $this->repository->save($handledRequest, $this->eventCounter);
    }
}
