<?php

declare(strict_types=1);

namespace EndeavourAgency\LaravelQueryInsights\Handlers;

use EndeavourAgency\LaravelQueryInsights\Contracts\Formatters\QueryStatsFormatterInterface;
use EndeavourAgency\LaravelQueryInsights\Contracts\HandlerInterface;
use Illuminate\Contracts\Config\Repository as Config;

abstract class AbstractHandler implements HandlerInterface
{
    protected QueryStatsFormatterInterface $formatter;

    public function __construct(
        protected Config $config,
    ) {
    }

    public function shouldRun(): bool
    {
        return $this->config->get('laravel-query-insights.enabled', true);
    }

    protected function getFormatter(): QueryStatsFormatterInterface
    {
        return $this->formatter ?? $this->getDefaultFormatter();
    }

    public function setFormatter(QueryStatsFormatterInterface $formatter): self
    {
        $this->formatter = $formatter;

        return $this;
    }

    abstract protected function getDefaultFormatter(): QueryStatsFormatterInterface;
}
