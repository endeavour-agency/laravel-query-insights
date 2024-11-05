# Laravel Query Insights
This package aims to provide query insights. By default, it comes with two handlers:
* `LogHandler`: Writes the executed queries to a log file
* `LighthouseResponseHandler`: Adds the executed queries to the extensions section of a [Lighthouse](https://lighthouse-php.com/) GraphQL response.

## Getting started
To get started, simply install the package.
```shell
composer require endeavour-agency/laravel-query-insights
```

Then, in a service provider, register the desired handlers with the `QueryCollectorInterface`.

```php
public function register(): void
{
    $collector = $this->app->make(QueryCollectorInterface::class);
        
    $collector
        ->registerHandler(
            $this->app->make(LogHandler::class),
        )
        ->registerHandler(
            $this->app->make(LighthouseResponseHandler::class),
        );        
}
```

## Configuration
The package comes with a configuration file which can be published through
```
php artisan vendor:publish --provider=EndeavourAgency\\LaravelQueryInsights\\Providers\\LaravelQueryInsightsProvider
```

Through the configuration file, the insights can be enabled or disabled. By default, insights will only
be enabled if `config('app.debug')` evaluates to `true`.

Alternatively, insights can be enabled or disabled through the `QUERY_INSIGHTS_ENABLED` env variable.

## Handlers
The package bundles a few handlers to get you started quickly.

### Log handler
The log handler will log all executed queries to a log file. It accepts an instance of the `Psr\Log\LoggerInterface`.
By default, it will log the queries to the default log channel (`config('logging.default')`).


**Example log handler output:**
```log
[2024-11-08 14:27:30] local.INFO: Queries: POST http://localhost:8000/graphql {"queries":[{"query":"select * from `users` where `email` = 'john@doe.com' and `users`.`deleted_at` is null limit 1","sql":"select * from `users` where `email` = ? and `users`.`deleted_at` is null limit 1","bindings":["john@doe.com"],"time":1.54}]}
[2024-11-08 14:27:31] local.INFO: Queries: POST http://localhost:8000/graphql {"queries":[{"query":"select * from `users` where `email` = 'jane@doe.com' and `users`.`deleted_at` is null limit 1","sql":"select * from `users` where `email` = ? and `users`.`deleted_at` is null limit 1","bindings":["jane@doe.com"],"time":1.51}]}
```

### Lighthouse Response Handler
The Lighthouse response handler will add executed queries to the extensions section of the GraphQL responses bodies,
when using [Lighthouse](https://lighthouse-php.com/).

**Example Lighthouse response handler output:**
```json
{
  "data": {
    "me": {
      "email": "john@doe.com"
    }
  },
  "extensions": {
    "queries": [
      {
        "query": "select * from `oauth_access_tokens` where `id` = '******' limit 1",
        "sql": "select * from `oauth_access_tokens` where `id` = ? limit 1",
        "bindings": [
          "******"
        ],
        "time": 1.51
      },
      {
        "query": "select * from `users` where `id` = '17' and `users`.`deleted_at` is null limit 1",
        "sql": "select * from `users` where `id` = ? and `users`.`deleted_at` is null limit 1",
        "bindings": [
          "17"
        ],
        "time": 0.45
      }
    ]
  }
}
```

### Custom handlers
To create your own handler, simply create a class that implements the `EndeavourAgency\LaravelQueryInsights\Contracts\HandlerInterface`
interface. Then, register it like you would with the default handlers (see [Getting started](#getting-started)).
