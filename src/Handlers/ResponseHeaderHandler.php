<?php

declare(strict_types=1);

namespace EndeavourAgency\LaravelQueryInsights\Handlers;

use EndeavourAgency\LaravelQueryInsights\Contracts\Formatters\QueryStatsFormatterInterface;
use EndeavourAgency\LaravelQueryInsights\DataObjects\QueryStats;
use EndeavourAgency\LaravelQueryInsights\Formatters\QueryTimeOnlyFormatter;
use Illuminate\Routing\Events\ResponsePrepared;

class ResponseHeaderHandler extends AbstractHandler
{
    public function handle(QueryStats $queryStats, $event): void
    {
        if (! $event instanceof ResponsePrepared) {
            return;
        }

        $response = $event->response;

        $response->headers->add($this->getFormatter()->format($queryStats));
    }

    public function eventTrigger(): string
    {
        return ResponsePrepared::class;
    }

    protected function getDefaultFormatter(): QueryStatsFormatterInterface
    {
        return new QueryTimeOnlyFormatter();
    }
}
