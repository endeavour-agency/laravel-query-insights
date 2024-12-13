<?php

declare(strict_types=1);

namespace EndeavourAgency\LaravelQueryInsights\Contracts;

use EndeavourAgency\LaravelQueryInsights\Contracts\Formatters\QueryStatsFormatterInterface;
use EndeavourAgency\LaravelQueryInsights\DataObjects\QueryStats;

interface HandlerInterface
{
    /**
     * @param QueryStats $queryStats
     * @param $event
     * @return void
     */
    public function handle(QueryStats $queryStats, $event): void;

    /**
     * The event that should trigger execution of the handle method.
     *
     * @return string
     */
    public function eventTrigger(): string;

    /**
     * Whether this handler should be run or not. This can be decided
     * on a handler level. For instance, queries may be logged safely
     * in a production environment, but adding them to an HTTP response
     * should likely be limited to debugging / testing.
     *
     * @return bool
     */
    public function shouldRun(): bool;

    public function setFormatter(QueryStatsFormatterInterface $formatter): self;
}
