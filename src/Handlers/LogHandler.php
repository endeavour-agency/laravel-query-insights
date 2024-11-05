<?php

declare(strict_types=1);

namespace EndeavourAgency\LaravelQueryInsights\Handlers;

use EndeavourAgency\LaravelQueryInsights\DataObjects\QueryStats;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Http\Request;
use Psr\Log\LoggerInterface;

class LogHandler extends AbstractHandler
{
    public function __construct(
        protected Repository $config,
        protected LoggerInterface $logger,
    ) {
        parent::__construct($this->config);
    }

    public function handle(QueryStats $queryStats, $event): void
    {
        $this->logger->info('Queries: ' . $this->stringifyRequest($queryStats->getRequest()), [
            'queries' => $queryStats->getQueries()->toArray(),
        ]);
    }

    protected function stringifyRequest(Request $request): string
    {
        return "{$request->method()} {$request->fullUrl()}";
    }

    public function eventTrigger(): string
    {
        return RequestHandled::class;
    }
}
