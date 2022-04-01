<?php declare(strict_types=1);

namespace Dive\Nova\Linkable;

use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Nova;

class FieldServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Nova::serving(static function () {
            Nova::script('flexible-url-field', __DIR__ . '/../dist/js/field.js');
            Nova::style('flexible-url-field', __DIR__ . '/../dist/css/field.css');
        });

        if ($this->app->runningInConsole()) {
            $this->registerMigrations();
        }
    }

    private function registerMigrations()
    {
        $migration = 'create_linkables_tables.php';
        $doesntExist = Collection::make(glob($this->app->databasePath('migrations/*.php')))
            ->every(fn ($filename) => ! str_ends_with($filename, $migration));

        if ($doesntExist) {
            $timestamp = date('Y_m_d_His', time());
            $stub = __DIR__ . "/../database/migrations/{$migration}.stub";

            $this->publishes([
                $stub => $this->app->databasePath("migrations/{$timestamp}_{$migration}"),
            ], 'migrations');
        }
    }
}
