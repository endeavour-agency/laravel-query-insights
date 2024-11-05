<?php

declare(strict_types=1);

namespace Integration\Handlers;

use EndeavourAgency\LaravelQueryInsights\Collectors\QueryCollector;
use EndeavourAgency\LaravelQueryInsights\Contracts\Collectors\QueryCollectorInterface;
use EndeavourAgency\LaravelQueryInsights\Handlers\LogHandler;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Contracts\Events\Dispatcher as Events;
use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Psr\Log\LoggerInterface;
use Tests\Integration\AbstractIntegrationTestCase;

class LogHandlerTest extends AbstractIntegrationTestCase
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
    public function it_logs_queries_if_insights_are_enabled(): void
    {
        $config = $this->app->make(Config::class);
        $config->set('laravel-query-insights.enabled', true);

        $logger = Mockery::mock(LoggerInterface::class);

        $logHandler = new LogHandler(
            $config,
            $logger,
        );

        $logger
            ->shouldReceive('info')
            ->once()
            ->withArgs(function (string $message, array $context): bool {
                return $message === 'Queries: GET http://localhost'
                    && array_key_exists('queries', $context)
                    && count($context['queries']) === 2
                    && $context['queries'][0]['query'] === 'select * from "users" where "id" = 1'
                    && $context['queries'][0]['sql'] === 'select * from "users" where "id" = ?'
                    && $context['queries'][0]['bindings'] === [1]
                    && $context['queries'][1]['query'] === 'insert into "users" ("email", "name", "password") values (\'foobar\', \'John Doe\', \'secret\')'
                    && $context['queries'][1]['sql'] === 'insert into "users" ("email", "name", "password") values (?, ?, ?)'
                    && $context['queries'][1]['bindings'] === [
                        'foobar',
                        'John Doe',
                        'secret',
                    ];
            });

        $this->collector->clear();

        $this->collector->registerHandler($logHandler);

        $this->performQueries()->triggerHandler();
    }

    #[Test]
    public function it_does_not_logs_queries_if_insights_are_disabled(): void
    {
        $config = $this->app->make(Config::class);
        $config->set('laravel-query-insights.enabled', false);

        $logger = Mockery::mock(LoggerInterface::class);

        $logHandler = new LogHandler(
            $config,
            $logger,
        );

        $logger->shouldNotReceive('info');

        $this->collector->clear();

        $this->collector->registerHandler($logHandler);

        $this->performQueries()->triggerHandler();
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

    protected function triggerHandler(): self
    {
        $this->events->dispatch(new RequestHandled(
            Mockery::mock(Request::class),
            Mockery::mock(Response::class),
        ));

        return $this;
    }
}
