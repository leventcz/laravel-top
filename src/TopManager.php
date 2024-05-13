<?php

declare(strict_types=1);

namespace Leventcz\Top;

use Leventcz\Top\Contracts\Repository;
use Leventcz\Top\Data\RequestSummary;
use Leventcz\Top\Data\RouteCollection;

readonly class TopManager
{
    public function __construct(
        private Repository $repository
    ) {
    }

    public function requests(): RequestSummary
    {
        return $this->repository->getRequestSummary();
    }

    public function routes(): RouteCollection
    {
        return $this->repository->getTopRoutes();
    }
}
