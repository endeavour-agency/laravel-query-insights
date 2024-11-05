<?php

declare(strict_types=1);

namespace Tests\Utilities\Traits;

use EndeavourAgency\LaravelQueryInsights\DataObjects\Query;
use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Grammars\Grammar;
use Mockery;
use Mockery\MockInterface;

trait MocksQuery
{
    protected function mockQuery(
        Connection&MockInterface $connection,
        string $query = 'select * from `users` where `id` = ? limit 1',
        string $resolvedQuery = 'select * from `users` where `id` = 15 limit 1',
        array $bindings = [15],
        float $time = 0.75,
        bool $resolvesRawQuery = false,
    ): Query {
        if (! $resolvesRawQuery) {
            return new Query($query, $bindings, $time, $connection);
        }

        $connection
            ->shouldReceive('prepareBindings')
            ->once()
            ->with($bindings)
            ->andReturn($bindings);

        $grammar = Mockery::mock(Grammar::class);
        $grammar
            ->shouldReceive('substituteBindingsIntoRawSql')
            ->once()
            ->with($query, $bindings)
            ->andReturn($resolvedQuery);

        $builder = Mockery::mock(Builder::class);
        $builder
            ->shouldReceive('getGrammar')
            ->once()
            ->andReturn($grammar);

        $connection
            ->shouldReceive('query')
            ->once()
            ->andReturn($builder);

        return new Query($query, $bindings, $time, $connection);
    }
}
