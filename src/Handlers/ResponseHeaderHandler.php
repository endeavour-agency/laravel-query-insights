<?php

declare(strict_types=1);

namespace EndeavourAgency\LaravelQueryInsights\Handlers;

use EndeavourAgency\LaravelQueryInsights\DataObjects\QueryStats;
use Illuminate\Routing\Events\ResponsePrepared;

class ResponseHeaderHandler extends AbstractHandler
{
    public function handle(QueryStats $queryStats, $event): void
    {
        if (! $event instanceof ResponsePrepared) {
            return;
        }

        $response = $event->response;

        $response->headers->add(['query-time' => $queryStats->getTime()]);
    }

    public function eventTrigger(): string
    {
        return ResponsePrepared::class;
    }
}
