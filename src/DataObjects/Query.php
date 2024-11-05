<?php

declare(strict_types=1);

namespace EndeavourAgency\LaravelQueryInsights\DataObjects;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Connection;

class Query implements Arrayable
{
    public function __construct(
        public readonly string $sql,
        public readonly array $bindings,
        public readonly float | null $time,
        public readonly Connection $connection,
    ) {
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

    public function toRawSql(): string
    {
        return $this->connection
            ->query()
            ->getGrammar()
            ->substituteBindingsIntoRawSql($this->sql, $this->connection->prepareBindings($this->bindings));
    }
}
