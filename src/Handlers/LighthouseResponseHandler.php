<?php

declare(strict_types=1);

namespace EndeavourAgency\LaravelQueryInsights\Handlers;

use EndeavourAgency\LaravelQueryInsights\DataObjects\QueryStats;
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

        $event->result->extensions += $queryStats->toArray();
    }

    public function eventTrigger(): string
    {
        return BuildExtensionsResponse::class;
    }
}
