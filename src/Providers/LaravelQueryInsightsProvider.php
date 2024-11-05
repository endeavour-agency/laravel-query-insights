<?php

declare(strict_types=1);

namespace EndeavourAgency\LaravelQueryInsights\Providers;

use EndeavourAgency\LaravelQueryInsights\Collectors\QueryCollector;
use EndeavourAgency\LaravelQueryInsights\Contracts\Collectors\QueryCollectorInterface;
use Illuminate\Support\ServiceProvider;

class LaravelQueryInsightsProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app->make(QueryCollectorInterface::class)->collect();

        $this->publishes([
            $this->packageRoot() . '/config/laravel-query-insights.php' => config_path('laravel-query-insights.php'),
        ]);
    }

    public function register(): void
    {
        $this->app->singleton(QueryCollectorInterface::class, QueryCollector::class);

        $this->mergeConfigFrom(
            $this->packageRoot() . '/config/laravel-query-insights.php', 'laravel-query-insights',
        );
    }

    protected function packageRoot(): string
    {
        return dirname(__DIR__);
    }
}
