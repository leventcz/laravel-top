<?php

declare(strict_types=1);

namespace Leventcz\Top\Listeners;

use Illuminate\Foundation\Http\Events\RequestHandled;
use Leventcz\Top\Data\HandledRequest;
use Leventcz\Top\Facades\State;
use Leventcz\Top\Facades\Top;

readonly class RequestListener
{
    public function requestHandled(RequestHandled $event): void
    {
        if (! $this->shouldRecord()) {
            return;
        }

        $startTime = defined('LARAVEL_START') ? LARAVEL_START : $event->request->server('REQUEST_TIME_FLOAT');
        $memory = memory_get_peak_usage(true) / 1024 / 1024;
        $duration = $startTime ? floor((microtime(true) - $startTime) * 1000) : null;

        $request = HandledRequest::fromArray([
            'uri' => sprintf('/%s', $event->request->route()?->uri()), // @phpstan-ignore-line
            'method' => $event->request->method(),
            'timestamp' => now()->getTimestamp(),
            'memory' => (int) $memory,
            'duration' => (int) $duration,
        ]);

        State::save($request);
    }

    public function subscribe(): array
    {
        return [
            RequestHandled::class => 'requestHandled',
        ];
    }

    private function shouldRecord(): bool
    {
        return config('top.recording_mode') === 'always' || Top::isRecording();
    }
}
