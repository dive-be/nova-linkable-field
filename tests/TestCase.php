<?php declare(strict_types=1);

namespace Tests;

use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app)
    {
        return [];
    }

    protected function defineDatabaseMigrations()
    {
        $this->loadMigrationsFrom(__DIR__ . '/Testbench/Database');
    }

    public function getEnvironmentSetUp($app)
    {
        $migration = include __DIR__ . '/../database/migrations/create_linkables_table.php.stub';
        $migration->up();
    }
}
