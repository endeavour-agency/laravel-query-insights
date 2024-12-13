<?php

declare(strict_types=1);

namespace EndeavourAgency\LaravelQueryInsights\Handlers;

use EndeavourAgency\LaravelQueryInsights\Contracts\Formatters\QueryStatsFormatterInterface;
use EndeavourAgency\LaravelQueryInsights\DataObjects\QueryStats;
use EndeavourAgency\LaravelQueryInsights\Formatters\SimpleArrayFormatter;
use Nuwave\Lighthouse\Events\BuildExtensionsResponse;

class LighthouseResponseHandler extends AbstractHandler
{
    /**
     * @param QueryStats $queryStats
     * @param $event
     * @return void
     */
    public function handle(QueryStats $queryStats, $event): void
    {
        if (! $event instanceof BuildExtensionsResponse) {
            return;
        }

        $event->result->extensions += $this->getFormatter()->format($queryStats);
    }

    public function eventTrigger(): string
    {
        return BuildExtensionsResponse::class;
    }

    protected function getDefaultFormatter(): QueryStatsFormatterInterface
    {
        return new SimpleArrayFormatter();
    }
}
