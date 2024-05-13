<?php

declare(strict_types=1);

namespace Leventcz\Top\Contracts;

use Leventcz\Top\Data\DatabaseSummary;
use Leventcz\Top\Data\EventCounter;
use Leventcz\Top\Data\HandledRequest;
use Leventcz\Top\Data\RequestSummary;
use Leventcz\Top\Data\RouteCollection;

interface Repository
{
    public function save(HandledRequest $request, EventCounter $eventCounter): void;

    public function getRequestSummary(): RequestSummary;

    public function getDatabaseSummary(): DatabaseSummary;

    public function getTopRoutes(): RouteCollection;
}
