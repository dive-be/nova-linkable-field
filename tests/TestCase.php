<?php declare(strict_types=1);

namespace Tests;

use Dive\Nova\Linkable\FieldServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Tests\Testbench\Models\NavItem;
use Tests\Testbench\Models\Page;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app)
    {
        return [
            FieldServiceProvider::class,
        ];
    }

    protected function defineDatabaseMigrations()
    {
        $this->loadMigrationsFrom(__DIR__ . '/Testbench/Database');
    }

    public function getEnvironmentSetUp($app)
    {
        $migration = include __DIR__ . '/../database/migrations/create_links_table.php.stub';
        $migration->up();

        config()->set(
            'nova-linkable-field.mapping',
            [NavItem::class => ['url' => Page::class]]
        );
    }
}
