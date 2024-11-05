<?php

declare(strict_types=1);

namespace Tests\Integration;

use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase;
use Orchestra\Testbench\Concerns\WithWorkbench;

abstract class AbstractIntegrationTestCase extends TestCase
{
    use WithWorkbench;
    use RefreshDatabase;

    protected function getEnvironmentSetUp($app): void
    {
        $config = $app->make(ConfigRepository::class);

        $config->set('lighthouse.schema_cache.enable', false);
    }
}
