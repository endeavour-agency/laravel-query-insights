<?php

declare(strict_types=1);

namespace EndeavourAgency\LaravelQueryInsights\Formatters;

use EndeavourAgency\LaravelQueryInsights\Contracts\Formatters\QueryStatsFormatterInterface;
use EndeavourAgency\LaravelQueryInsights\DataObjects\QueryStats;

class QueryTimeOnlyFormatter implements QueryStatsFormatterInterface
{
    public function format(QueryStats $queryStats): array
    {
        return ['query-time' => $queryStats->getTime()];
    }
}
