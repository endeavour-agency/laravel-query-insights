<?php

declare(strict_types=1);

namespace Tests\Integration\Collectors;

use EndeavourAgency\LaravelQueryInsights\Collectors\QueryCollector;
use EndeavourAgency\LaravelQueryInsights\Contracts\Collectors\QueryCollectorInterface;
use EndeavourAgency\LaravelQueryInsights\Contracts\HandlerInterface;
use EndeavourAgency\LaravelQueryInsights\DataObjects\Query;
use EndeavourAgency\LaravelQueryInsights\DataObjects\QueryStats;
use Illuminate\Contracts\Events\Dispatcher as Events;
use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\Integration\AbstractIntegrationTestCase;

class QueryCollectorTest extends AbstractIntegrationTestCase
{
    protected Events $events;
    protected QueryCollector $collector;

    protected function setUp(): void
    {
        parent::setUp();

        $this->events    = $this->app->make(Events::class);
        $this->collector = $this->app->make(QueryCollectorInterface::class);
    }

    #[Test]
    public function it_triggers_registered_handlers_if_insights_are_enabled(): void
    {
        $this->collector->clear();

        $event = new RequestHandled(
            Mockery::mock(Request::class),
            Mockery::mock(Response::class),
        );

        $handler = Mockery::mock(HandlerInterface::class);
        $handler
            ->shouldReceive('shouldRun')
            ->andReturnTrue();
        $handler
            ->shouldReceive('eventTrigger')
            ->andReturn(RequestHandled::class);
        $handler
            ->shouldReceive('handle')
            ->once()
            ->withArgs(function (QueryStats $queryStats, $event): bool {
                $queries = $queryStats->getQueries();
                /** @var Query $query1 */
                $query1 = $queries->get(0);
                /** @var Query $query2 */
                $query2 = $queries->get(1);

                return $event instanceof RequestHandled
                    && $queries->count() === 2
                    && $query1->toRawSql() === 'select * from "users" where "id" = 1'
                    && $query2->toRawSql() === 'insert into "users" ("email", "name", "password") values (\'foobar\', \'John Doe\', \'secret\')';
            });

        $this->collector->registerHandler($handler);

        $this->performQueries();

        $this->events->dispatch($event);
    }

    #[Test]
    public function it_does_not_trigger_handlers_if_insights_are_disabled(): void
    {
        $this->collector->clear();

        $event = new RequestHandled(
            Mockery::mock(Request::class),
            Mockery::mock(Response::class),
        );

        $handler = Mockery::mock(HandlerInterface::class);
        $handler
            ->shouldReceive('shouldRun')
            ->once()
            ->andReturnFalse();
        $handler->shouldNotReceive('handle');

        $this->collector->registerHandler($handler);

        $this->performQueries();

        $this->events->dispatch($event);
    }

    protected function performQueries(): self
    {
        DB::table('users')->where('id', 1)->get();
        DB::table('users')->insert([
            'email'    => 'foobar',
            'name'     => 'John Doe',
            'password' => 'secret',
        ]);

        return $this;
    }
}
