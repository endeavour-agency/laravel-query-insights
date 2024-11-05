<?php

declare(strict_types=1);

namespace EndeavourAgency\LaravelQueryInsights\Collectors;

use EndeavourAgency\LaravelQueryInsights\Contracts\Collectors\QueryCollectorInterface;
use EndeavourAgency\LaravelQueryInsights\Contracts\HandlerInterface;
use EndeavourAgency\LaravelQueryInsights\DataObjects\Query;
use EndeavourAgency\LaravelQueryInsights\DataObjects\QueryStats;
use Illuminate\Contracts\Events\Dispatcher as Events;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Http\Request;

class QueryCollector implements QueryCollectorInterface
{
    protected QueryStats $queryStats;

    /** @var array<int, HandlerInterface> */
    protected array $handlers = [];

    public function __construct(
        protected readonly Events $events,
        protected readonly Request $request,
    ) {
        $this->clear();
    }

    public function clear(): self
    {
        $this->queryStats = new QueryStats(
            $this->request,
        );

        return $this;
    }

    public function registerHandler(HandlerInterface $handler): self
    {
        $this->handlers[] = $handler;

        if (! $handler->shouldRun()) {
            return $this;
        }

        $this->events->listen($handler->eventTrigger(), function ($event) use ($handler) {
            $handler->handle($this->queryStats, $event);
        });

        return $this;
    }

    public function collect(): self
    {
        $this->events->listen(QueryExecuted::class, function (QueryExecuted $query) {
            $this->queryStats->addQuery(
                new Query(
                    $query->sql,
                    $query->bindings,
                    $query->time,
                    $query->connection,
                ),
            );
        });

        return $this;
    }
}
