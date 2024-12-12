<?php

declare(strict_types=1);

namespace Integration\Handlers;

use EndeavourAgency\LaravelQueryInsights\Collectors\QueryCollector;
use EndeavourAgency\LaravelQueryInsights\Contracts\Collectors\QueryCollectorInterface;
use EndeavourAgency\LaravelQueryInsights\Handlers\ResponseHeaderHandler;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Contracts\Events\Dispatcher as Events;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\Integration\AbstractIntegrationTestCase;

class ResponseHeaderHandlerTest extends AbstractIntegrationTestCase
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
    public function it_adds_queries_to_the_response_headers(): void
    {
        $config = $this->app->make(Config::class);
        $config->set('laravel-query-insights.enabled', true);

        $responseHeaderHandler = new ResponseHeaderHandler($config);

        $this->collector->clear();

        $this->collector->registerHandler($responseHeaderHandler);

        $response = $this->makeRequest();

        $response->assertHeader('query-time');
    }

    protected function makeRequest(): TestResponse
    {
        $this->app->make(Router::class)->get('users', function () {
            return DB::table('users')->get();
        });

        return $this->get('users');
    }
}
