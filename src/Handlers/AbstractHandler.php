<?php

declare(strict_types=1);

namespace EndeavourAgency\LaravelQueryInsights\Handlers;

use EndeavourAgency\LaravelQueryInsights\Contracts\HandlerInterface;
use Illuminate\Contracts\Config\Repository as Config;

abstract class AbstractHandler implements HandlerInterface
{
    public function __construct(
        protected Config $config,
    ) {
    }

    public function shouldRun(): bool
    {
        return $this->config->get('laravel-query-insights.enabled', true);
    }
}
