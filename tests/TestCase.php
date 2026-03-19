<?php

namespace Laravelldone\DbCleaner\Tests;

use Laravelldone\DbCleaner\DbCleanerServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    protected function getPackageProviders($app): array
    {
        return [
            DbCleanerServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'testing');
        config()->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        config()->set('db-cleaner.connection', 'testing');
        config()->set('db-cleaner.exclude_tables', [
            'migrations',
            'db_cleaner_scan_results',
        ]);
    }
}
