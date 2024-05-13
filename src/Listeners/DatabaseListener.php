<?php

declare(strict_types=1);

namespace Leventcz\Top\Listeners;

use Illuminate\Database\Events\QueryExecuted;
use Leventcz\Top\Facades\State;

class DatabaseListener
{
    public function queryExecuted(QueryExecuted $event): void
    {
        State::add('database-query-executed');
        State::add('database-query-execution-time', (int) $event->time);
    }

    public function subscribe(): array
    {
        return [
            QueryExecuted::class => 'queryExecuted',
        ];
    }
}
