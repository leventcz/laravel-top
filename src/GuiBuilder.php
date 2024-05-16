<?php

declare(strict_types=1);

namespace Leventcz\Top;

use Leventcz\Top\Data\CacheSummary;
use Leventcz\Top\Data\DatabaseSummary;
use Leventcz\Top\Data\RequestSummary;
use Leventcz\Top\Data\Route;
use Leventcz\Top\Data\RouteCollection;
use Leventcz\Top\Extensions\BufferedOutput;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Output\OutputInterface;

class GuiBuilder
{
    private ?OutputInterface $output = null;

    private ?RequestSummary $requestSummary = null;

    private ?DatabaseSummary $databaseSummary = null;

    private ?CacheSummary $cacheSummary = null;

    private ?RouteCollection $topRoutes = null;

    public function setOutput(OutputInterface $output): static
    {
        $this->output = $output;

        return $this;
    }

    public function setRequestSummary(RequestSummary $requestSummary): static
    {
        $this->requestSummary = $requestSummary;

        return $this;
    }

    public function setDatabaseSummary(DatabaseSummary $databaseSummary): static
    {
        $this->databaseSummary = $databaseSummary;

        return $this;
    }

    public function setCacheSummary(CacheSummary $cacheSummary): static
    {
        $this->cacheSummary = $cacheSummary;

        return $this;
    }

    public function setTopRoutes(RouteCollection $topRoutes): static
    {
        $this->topRoutes = $topRoutes;

        return $this;
    }

    public function render(): void
    {
        $table = new Table($this->output);
        $table->setStyle('compact');
        $table->setColumnWidths([40, 40, 40]);

        $table->setRows([
            [
                new TableSeparator(),
            ],
            [
                new TableCell($this->renderRequestsCard()->fetch(), ['colspan' => 1]),
                new TableCell($this->renderDatabaseCard()->fetch(), ['colspan' => 1]),
                new TableCell($this->renderCacheCard()->fetch(), ['colspan' => 1]),
            ],
            [
                new TableCell($this->renderTopRequestsTable()->fetch(), ['colspan' => 3]),
            ],
        ]);

        $table->render();
    }

    private function renderRequestsCard(): BufferedOutput
    {
        return $this->createCard('HTTP', ['Requests/sec', 'Avg. Memory(MB)', 'Avg. Duration(ms)'], [
            number_format($this->requestSummary?->averageRequestPerSecond ?? 0, 2),
            number_format($this->requestSummary?->averageMemoryUsage ?? 0, 2),
            number_format($this->requestSummary?->averageDuration ?? 0, 2),
        ]);
    }

    private function renderCacheCard(): BufferedOutput
    {
        return $this->createCard('Cache', ['Hit/sec', 'Miss/sec', 'Write/sec'], [
            number_format($this->cacheSummary?->averageHitPerSecond ?? 0, 2),
            number_format($this->cacheSummary?->averageMissPerSecond ?? 0, 2),
            number_format($this->cacheSummary?->averageWritePerSecond ?? 0, 2),
        ]);
    }

    private function renderDatabaseCard(): BufferedOutput
    {
        return $this->createCard('Database', ['Queries/sec', 'Avg. Exec. Time(ms)'], [
            number_format($this->databaseSummary?->averageQueryPerSecond ?? 0, 2),
            number_format($this->databaseSummary?->averageQueryDuration ?? 0, 2),
        ]);
    }

    private function renderTopRequestsTable(): BufferedOutput
    {
        $output = new BufferedOutput();
        $table = new Table($output);
        $table->setStyle('compact');
        $table->setHeaders(['Method', 'URI', 'Avg. Memory(MB)', 'Avg. Duration(ms)', 'Req./sec']);
        $table->setColumnWidths([10, 45, 20, 20, 20]);

        $this
            ->topRoutes
            ?->each(function (Route $route) use ($table) {
                $table->addRow([
                    $route->method,
                    $route->uri,
                    number_format($route->averageMemoryUsage, 2),
                    number_format($route->averageDuration, 2),
                    number_format($route->averageRequestPerSecond, 2),
                ]);
            });
        $table->render();

        return $output;
    }

    private function createCard(string $title, array $headers, array $rows): BufferedOutput
    {
        $output = new BufferedOutput();
        $output->writeln("<fg=yellow>  $title</>");

        $table = new Table($output);
        $table->setStyle('box');
        $table->setHorizontal();
        $table->setHeaders($headers);
        $table->setRows([$rows]);
        $table->render();

        return $output;
    }

    public function enterAlternateScreen(): static
    {
        $this->output?->write("\033[?1049h");

        return $this;
    }

    public function exitAlternateScreen(): static
    {
        $this->output?->write("\033[?1049l");

        return $this;
    }

    public function hideCursor(): static
    {
        $this->output?->write("\033[?25l");

        return $this;
    }

    public function showCursor(): static
    {
        $this->output?->write("\033[?25h");

        return $this;
    }

    public function moveToTop(): static
    {
        $this->output?->write("\033[H");

        return $this;
    }
}
