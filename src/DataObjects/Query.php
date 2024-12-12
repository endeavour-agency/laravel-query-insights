<?php

declare(strict_types=1);

namespace EndeavourAgency\LaravelQueryInsights\DataObjects;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Connection;

class Query implements Arrayable
{
    public function __construct(
        protected readonly string $sql,
        protected readonly array $bindings,
        protected readonly float | null $time,
        protected readonly Connection $connection,
    ) {
    }

    public function getSql(): string
    {
        return $this->sql;
    }

    public function getBindings(): array
    {
        return $this->bindings;
    }

    public function getTime(): ?float
    {
        return $this->time;
    }

    public function toRawSql(): string
    {
        return $this->connection
            ->query()
            ->getGrammar()
            ->substituteBindingsIntoRawSql($this->sql, $this->connection->prepareBindings($this->bindings));
    }

    public function toArray(): array
    {
        return [
            'query'    => $this->toRawSql(),
            'sql'      => $this->sql,
            'bindings' => $this->bindings,
            'time'     => $this->time,
        ];
    }
}
