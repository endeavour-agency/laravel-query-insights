<?php

declare(strict_types=1);

namespace EndeavourAgency\LaravelQueryInsights\Contracts\Collectors;

use EndeavourAgency\LaravelQueryInsights\Contracts\HandlerInterface;

interface QueryCollectorInterface
{
    public function registerHandler(HandlerInterface $handler): self;

    public function collect(): self;
}
