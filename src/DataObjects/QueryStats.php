<?php

declare(strict_types=1);

namespace EndeavourAgency\LaravelQueryInsights\DataObjects;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class QueryStats implements Arrayable
{
    /**
     * @var Collection<Query>
     */
    protected Collection $queries;

    public function __construct(
        protected readonly Request $request,
    ) {
        $this->queries = new Collection();
    }

    public function getQueries(): Collection
    {
        return $this->queries;
    }

    public function addQuery(Query $query): self
    {
        $this->queries->push($query);

        return $this;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function toArray(): array
    {
        return $this->queries->toArray();
    }
}
