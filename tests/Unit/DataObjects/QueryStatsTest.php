<?php

declare(strict_types=1);

namespace Tests\Unit\DataObjects;

use EndeavourAgency\LaravelQueryInsights\DataObjects\Query;
use EndeavourAgency\LaravelQueryInsights\DataObjects\QueryStats;
use Illuminate\Http\Request;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\Unit\AbstractUnitTestCase;
use Tests\Utilities\Traits\MocksQuery;

class QueryStatsTest extends AbstractUnitTestCase
{
    use MocksQuery;

    #[Test]
    public function it_returns_the_request(): void
    {
        $request    = Mockery::mock(Request::class);
        $queryStats = new QueryStats(
            $request,
        );

        $this->assertSame($request, $queryStats->getRequest());
    }

    #[Test]
    public function it_adds_and_gets_queries(): void
    {
        $queryStats = new QueryStats(
            Mockery::mock(Request::class),
        );

        $query1 = Mockery::mock(Query::class);
        $query2 = Mockery::mock(Query::class);

        $queryStats
            ->addQuery($query1)
            ->addQuery($query2);

        $queries = $queryStats->getQueries();

        $this->assertCount(2, $queries);
        static::assertSame($query1, $queries->get(0));
        static::assertSame($query2, $queries->get(1));
    }

    #[Test]
    public function it_returns_the_query_time(): void
    {
        $queryStats = new QueryStats(
            Mockery::mock(Request::class),
        );

        $query1 = Mockery::mock(Query::class, ['getTime' => 0.75]);
        $query2 = Mockery::mock(Query::class, ['getTime' => 0.4]);

        $queryStats
            ->addQuery($query1)
            ->addQuery($query2);

        static::assertSame(1.15, $queryStats->getTime());
    }

    #[Test]
    public function it_casts_query_stats_to_an_array(): void
    {
        $queryStats = new QueryStats(
            Mockery::mock(Request::class),
        );

        $query1 = Mockery::mock(Query::class, ['getTime' => 0.75]);
        $query1
            ->shouldReceive('toArray')
            ->once()
            ->andReturn([
                'query'    => 'select * from `users` where `id` = 15 limit 1',
                'sql'      => 'select * from `users` where `id` = ? limit 1',
                'bindings' => [
                    15,
                ],
                'time'     => 0.75,
            ]);

        $query2 = Mockery::mock(Query::class, ['getTime' => 0.4]);
        $query2
            ->shouldReceive('toArray')
            ->once()
            ->andReturn([
                'query'    => 'select * from `users` where `id` = 3 limit 1',
                'sql'      => 'select * from `users` where `id` = ? limit 1',
                'bindings' => [
                    3,
                ],
                'time'     => 0.4,
            ]);

        $queryStats
            ->addQuery($query1)
            ->addQuery($query2);

        $castedQueryStats = $queryStats->toArray();

        static::assertSame([
            'queries'    => [
                [
                    'query'    => 'select * from `users` where `id` = 15 limit 1',
                    'sql'      => 'select * from `users` where `id` = ? limit 1',
                    'bindings' => [
                        15,
                    ],
                    'time'     => 0.75,
                ],
                [
                    'query'    => 'select * from `users` where `id` = 3 limit 1',
                    'sql'      => 'select * from `users` where `id` = ? limit 1',
                    'bindings' => [
                        3,
                    ],
                    'time'     => 0.4,
                ],
            ],
            'query-time' => 1.15,
        ],
            $castedQueryStats,
        );
    }
}
