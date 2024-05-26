<?php

declare(strict_types=1);

namespace Leventcz\Top;

use Leventcz\Top\Contracts\Repository;
use Leventcz\Top\Data\CacheSummary;
use Leventcz\Top\Data\DatabaseSummary;
use Leventcz\Top\Data\RequestSummary;
use Leventcz\Top\Data\RouteCollection;

readonly class TopManager
{
    public function __construct(
        private Repository $repository
    ) {
    }

    public function http(): RequestSummary
    {
        return $this->repository->getRequestSummary();
    }

    public function database(): DatabaseSummary
    {
        return $this->repository->getDatabaseSummary();
    }

    public function cache(): CacheSummary
    {
        return $this->repository->getCacheSummary();
    }

    public function routes(): RouteCollection
    {
        return $this->repository->getTopRoutes();
    }

    public function startRecording(int $duration = 5): void
    {
        $this->repository->setRecorder($duration);
    }

    public function stopRecording(): void
    {
        $this->repository->deleteRecorder();
    }

    public function isRecording(): bool
    {
        return $this->repository->recorderExists();
    }
}
