<?php declare(strict_types=1);

namespace Dive\FlexibleUrlField;

use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Events\ServingNova;
use Laravel\Nova\Nova;

class FieldServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Nova::serving(function (ServingNova $event) {
            Nova::script('flexible-url-field', __DIR__ . '/../dist/js/field.js');
            Nova::style('flexible-url-field', __DIR__ . '/../dist/css/field.css');
        });

        $this->publishes([
            __DIR__ . '/../database/migrations/' => database_path('migrations'),
        ], 'flexible-url-migrations');
    }

    public function register()
    {
        //
    }
}
