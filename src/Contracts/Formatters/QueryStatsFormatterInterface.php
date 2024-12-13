<?php

declare(strict_types=1);

namespace EndeavourAgency\LaravelQueryInsights\Contracts\Formatters;

use EndeavourAgency\LaravelQueryInsights\DataObjects\QueryStats;

interface QueryStatsFormatterInterface
{
    public function format(QueryStats $queryStats): mixed;
}
