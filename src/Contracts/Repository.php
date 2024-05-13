<?php

declare(strict_types=1);

namespace Leventcz\Top\Contracts;

use Leventcz\Top\Data\EventCollection;
use Leventcz\Top\Data\HandledRequest;
use Leventcz\Top\Data\RequestSummary;
use Leventcz\Top\Data\RouteCollection;

interface Repository
{
    public function save(HandledRequest $handledRequest, EventCollection $eventCollection): void;

    public function getRequestSummary(): RequestSummary;

    public function getTopRoutes(): RouteCollection;
}
