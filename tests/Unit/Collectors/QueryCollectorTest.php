<?php

declare(strict_types=1);

namespace Tests\Unit\Collectors;

use EndeavourAgency\LaravelQueryInsights\Collectors\QueryCollector;
use EndeavourAgency\LaravelQueryInsights\Contracts\HandlerInterface;
use Illuminate\Contracts\Events\Dispatcher as EventDispatcher;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Http\Request;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\Unit\AbstractUnitTestCase;
use Tests\Utilities\Events\TestEvent;

class QueryCollectorTest extends AbstractUnitTestCase
{
    #[Test]
    public function it_listens_to_query_executed_events_and_registers_handler_triggers(): void
    {
        $events = Mockery::mock(EventDispatcher::class);
        $events
            ->shouldReceive('listen')
            ->once()
            ->withArgs(function ($event) {
                return $event === QueryExecuted::class;
            });
        $events
            ->shouldReceive('listen')
            ->once()
            ->withArgs(function ($event) {
                return $event === TestEvent::class;
            });

        $collector = new QueryCollector(
            $events,
            Mockery::mock(Request::class),
        );

        $handler1 = Mockery::mock(HandlerInterface::class);
        $handler1
            ->shouldReceive('shouldRun')
            ->once()
            ->andReturnTrue();
        $handler1
            ->shouldReceive('eventTrigger')
            ->once()
            ->andReturn(TestEvent::class);

        $handler2 = Mockery::mock(HandlerInterface::class);
        $handler2
            ->shouldReceive('shouldRun')
            ->once()
            ->andReturnFalse();

        $collector
            ->registerHandler(
                $handler1,
            )->registerHandler(
                $handler2,
            )->collect();
    }
}
