<?php

declare(strict_types=1);

namespace Tests\Unit\Handlers;

use EndeavourAgency\LaravelQueryInsights\Contracts\Formatters\QueryStatsFormatterInterface;
use EndeavourAgency\LaravelQueryInsights\DataObjects\QueryStats;
use EndeavourAgency\LaravelQueryInsights\Handlers\LighthouseResponseHandler;
use GraphQL\Executor\ExecutionResult;
use Illuminate\Contracts\Config\Repository as Config;
use Mockery;
use Nuwave\Lighthouse\Events\BuildExtensionsResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\Unit\AbstractUnitTestCase;

class LighthouseResponseHandlerTest extends AbstractUnitTestCase
{
    #[Test]
    public function it_returns_its_event_trigger(): void
    {
        $config  = Mockery::mock(Config::class);
        $handler = new LighthouseResponseHandler($config);

        static::assertSame(BuildExtensionsResponse::class, $handler->eventTrigger());
    }

    #[Test]
    public function it_adds_queries_to_the_lighthouse_response(): void
    {
        $config    = Mockery::mock(Config::class);
        $formatter = Mockery::mock(QueryStatsFormatterInterface::class);

        $handler = new LighthouseResponseHandler(
            $config,
        );
        $handler->setFormatter($formatter);
        $event   = new BuildExtensionsResponse(new ExecutionResult([]));

        $queryStats = Mockery::mock(QueryStats::class);

        $formatter
            ->shouldReceive('format')
            ->once()
            ->with($queryStats)
            ->andReturn([
                'queries'    => [
                    'bar' => 'test',
                ],
                'query-time' => 0.5,
            ]);

        $handler->handle(
            $queryStats,
            $event,
        );

        static::assertSame([
            'queries'    => [
                'bar' => 'test',
            ],
            'query-time' => 0.5,
        ], $event->result->extensions);
    }
}
