<?php

declare(strict_types=1);

namespace EndeavourAgency\LaravelQueryInsights\Formatters;

use EndeavourAgency\LaravelQueryInsights\Contracts\Formatters\QueryStatsFormatterInterface;
use EndeavourAgency\LaravelQueryInsights\DataObjects\QueryStats;

class SimpleArrayFormatter implements QueryStatsFormatterInterface
{
    public function format(QueryStats $queryStats): array
    {
        return [
            'queries'    => $queryStats->getQueries()->toArray(),
            'query-time' => $queryStats->getTime(),
        ];
    }
}
