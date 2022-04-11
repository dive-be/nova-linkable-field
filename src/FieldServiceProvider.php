<?php declare(strict_types=1);

namespace Dive\Nova\Linkable;

use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Nova;

class FieldServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->registerConfig();

        if ($this->app->runningInConsole()) {
            $this->registerMigrations();
        }

        if ($this->app->runningUnitTests()) {
            return;
        }

        Nova::serving(static function () {
            Nova::script('flexible-url-field', __DIR__ . '/../dist/js/field.js');
            Nova::style('flexible-url-field', __DIR__ . '/../dist/css/field.css');
        });
    }

    public function register()
    {
        $this->app->singleton(LinkRepository::class, function () {
            $linkModelClass = config('nova-linkable-field.model');

            return new LinkRepository(new $linkModelClass());
        });

        $this->app->singleton(ModelTargetProvider::class, function () {
            return new ModelTargetProvider(config('nova-linkable-field.mapping'));
        });
    }

    private function registerConfig()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/nova-linkable-field.php', 'nova-linkable-field');

        $this->publishes([
            __DIR__ . '../config/nova-linkable-field.php' => config_path('nova-linkable-field.php')
        ]);
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
