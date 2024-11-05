<?php

declare(strict_types=1);

namespace Integration\Handlers;

use EndeavourAgency\LaravelQueryInsights\Collectors\QueryCollector;
use EndeavourAgency\LaravelQueryInsights\Contracts\Collectors\QueryCollectorInterface;
use EndeavourAgency\LaravelQueryInsights\Handlers\LighthouseResponseHandler;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Contracts\Events\Dispatcher as Events;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Nuwave\Lighthouse\Testing\MocksResolvers;
use Nuwave\Lighthouse\Testing\UsesTestSchema;
use PHPUnit\Framework\Attributes\Test;
use Tests\Integration\AbstractIntegrationTestCase;

class LighthouseResponseHandlerTest extends AbstractIntegrationTestCase
{
    use MakesGraphQLRequests;
    use MocksResolvers;
    use UsesTestSchema;

    protected Events $events;
    protected QueryCollector $collector;

    protected function setUp(): void
    {
        parent::setUp();

        $this->events    = $this->app->make(Events::class);
        $this->collector = $this->app->make(QueryCollectorInterface::class);

        $this->setUpTestSchema();
    }

    #[Test]
    public function it_adds_queries_to_the_extensions_section_of_the_graphql_response_if_insights_are_enabled(): void
    {
        $config = $this->app->make(Config::class);
        $config->set('laravel-query-insights.enabled', true);

        $handler = new LighthouseResponseHandler($config);

        $this->collector->clear();

        $this->collector->registerHandler($handler);

        $response = $this->makeRequest();

        $response->assertJson([
            'extensions' => [
                'queries' => [
                    [
                        "query"    => 'select * from "users" where "id" = \'a6789e46-3f88-46d6-9e14-cc0abf91ce92\' limit 1',
                        "sql"      => 'select * from "users" where "id" = ? limit 1',
                        "bindings" => [
                            "a6789e46-3f88-46d6-9e14-cc0abf91ce92",
                        ],
                    ],
                ],
            ],
        ]);
    }

    #[Test]
    public function it_does_not_add_queries_to_the_extensions_section_of_the_graphql_response_if_insights_are_disabled(): void
    {
        $config = $this->app->make(Config::class);
        $config->set('laravel-query-insights.enabled', false);

        $handler = new LighthouseResponseHandler($config);

        $this->collector->clear();

        $this->collector->registerHandler($handler);

        $response = $this->makeRequest();

        $response->assertJsonMissing([
            'extensions' => [
                'queries' => [
                    [
                        "query"    => 'select * from "users" where "id" = \'a6789e46-3f88-46d6-9e14-cc0abf91ce92\' limit 1',
                        "sql"      => 'select * from "users" where "id" = ? limit 1',
                        "bindings" => [
                            "a6789e46-3f88-46d6-9e14-cc0abf91ce92",
                        ],
                    ],
                ],
            ],
        ]);
    }

    protected function makeRequest(): TestResponse
    {
        $this->mockResolver(fn () => DB::table('users')->where('id', 'a6789e46-3f88-46d6-9e14-cc0abf91ce92')->first());

        $this->schema = <<<GRAPHQL
type User {
    id: ID!
}

type Query {
    user(id: ID! @whereKey): User @mock
}
GRAPHQL;

        return $this->graphQL(/** @lang GraphQL */ '
            query {
                user(id: "a6789e46-3f88-46d6-9e14-cc0abf91ce92") {
                    id
                }
            }
        ');
    }
}
