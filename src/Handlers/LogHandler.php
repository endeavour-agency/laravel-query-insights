<?php

declare(strict_types=1);

namespace EndeavourAgency\LaravelQueryInsights\Handlers;

use EndeavourAgency\LaravelQueryInsights\Contracts\Formatters\QueryStatsFormatterInterface;
use EndeavourAgency\LaravelQueryInsights\DataObjects\QueryStats;
use EndeavourAgency\LaravelQueryInsights\Formatters\SimpleArrayFormatter;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Http\Request;
use Psr\Log\LoggerInterface;

class LogHandler extends AbstractHandler
{
    public function __construct(
        protected Config $config,
        protected LoggerInterface $logger,
    ) {
        parent::__construct($this->config);
    }

    public function handle(QueryStats $queryStats, $event): void
    {
        $this->logger->info(
            'Queries: ' . $this->stringifyRequest($queryStats->getRequest()),
            $this->getFormatter()->format($queryStats),
        );
    }

    protected function stringifyRequest(Request $request): string
    {
        return "{$request->method()} {$request->fullUrl()}";
    }

    public function eventTrigger(): string
    {
        return RequestHandled::class;
    }

    protected function getDefaultFormatter(): QueryStatsFormatterInterface
    {
        return new SimpleArrayFormatter();
    }
}
