<?php

declare(strict_types=1);

namespace Leventcz\Top\Commands;

use Illuminate\Console\Command;
use Leventcz\Top\Facades\Top;
use Leventcz\Top\GuiBuilder;
use React\EventLoop\Loop;

class TopCommand extends Command
{
    protected $signature = 'top';

    protected $description = 'Monitor your application in real time';

    public function handle(GuiBuilder $guiBuilder): void
    {
        $guiBuilder
            ->setOutput($this->output)
            ->enterAlternateScreen()
            ->hideCursor()
            ->render();

        Loop::addPeriodicTimer(0.5,
            function () use ($guiBuilder) {
                $guiBuilder
                    ->moveToTop()
                    ->render();
            });

        Loop::addPeriodicTimer(1,
            function () use ($guiBuilder) {
                $guiBuilder
                    ->setRequestSummary(Top::requests())
                    ->setTopRoutes(Top::routes());
            });

        pcntl_signal(SIGINT, function () use ($guiBuilder) {
            $guiBuilder
                ->exitAlternateScreen()
                ->showCursor();
            exit();
        });
    }
}
