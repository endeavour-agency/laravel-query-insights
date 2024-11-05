<?php

declare(strict_types=1);

namespace Tests\Unit\DataObjects;

use EndeavourAgency\LaravelQueryInsights\DataObjects\Query;
use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Grammars\Grammar;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\Unit\AbstractUnitTestCase;

class QueryTest extends AbstractUnitTestCase
{
    protected Connection&MockInterface $connection;
    protected Query $query;

    protected function setUp(): void
    {
        parent::setUp();

        $this->connection = Mockery::mock(Connection::class);

        $this->query = new Query(
            'select * from `users` where `id` = ? limit 1',
            [
                15,
            ],
            0.75,
            $this->connection,
        );
    }

    #[Test]
    public function it_returns_the_raw_sql_string(): void
    {
        $this->expectRawSqlResolving();

        $result = $this->query->toRawSql();

        static::assertSame('select * from `users` where `id` = 15 limit 1', $result);
    }

    #[Test]
    public function it_casts_to_an_array(): void
    {
        $this->expectRawSqlResolving();

        $result = $this->query->toArray();

        static::assertSame([
            'query'    => 'select * from `users` where `id` = 15 limit 1',
            'sql'      => 'select * from `users` where `id` = ? limit 1',
            'bindings' => [
                15,
            ],
            'time' => 0.75,
        ], $result);
    }

    protected function expectRawSqlResolving(): void
    {
        $this->connection
            ->shouldReceive('prepareBindings')
            ->once()
            ->with([15])
            ->andReturn([15])
            ->getMock()
            ->shouldReceive('query')
            ->once()
            ->andReturn(
                Mockery::mock(Builder::class)
                    ->shouldReceive('getGrammar')
                    ->once()
                    ->andReturn(
                        Mockery::mock(Grammar::class)
                            ->shouldReceive('substituteBindingsIntoRawSql')
                            ->once()
                            ->with('select * from `users` where `id` = ? limit 1', [15])
                            ->andReturn('select * from `users` where `id` = 15 limit 1')
                            ->getMock(),
                    )
                    ->getMock(),
            );
    }
}
