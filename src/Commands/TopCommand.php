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
        $this->feed($guiBuilder);

        $guiBuilder
            ->setOutput($this->output)
            ->enterAlternateScreen()
            ->hideCursor()
            ->render();

        Loop::addPeriodicTimer(0.5, fn () => $guiBuilder->moveToTop()->render());
        Loop::addPeriodicTimer(1, fn () => $this->feed($guiBuilder));

        pcntl_signal(SIGINT, function () use ($guiBuilder) {
            $guiBuilder
                ->exitAlternateScreen()
                ->showCursor();
            exit(0);
        });
    }

    private function feed(GuiBuilder $guiBuilder): void
    {
        Top::startRecording();

        $guiBuilder
            ->setRequestSummary(Top::http())
            ->setDatabaseSummary(Top::database())
            ->setCacheSummary(Top::cache())
            ->setTopRoutes(Top::routes());
    }
}
