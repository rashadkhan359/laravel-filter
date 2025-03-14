<?php

namespace RashadKhan\LaravelFilter;

use Illuminate\Support\ServiceProvider;
use RashadKhan\LaravelFilter\Contracts\FilterDriverInterface;
use RashadKhan\LaravelFilter\Drivers\EloquentDriver;
use RashadKhan\LaravelFilter\Drivers\MongoDriver;

class QueryFilterServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // Register the config file
        $this->mergeConfigFrom(
            __DIR__.'/../config/laravelfilter.php', 'laravelfilter'
        );

        // Register the filter manager
        $this->app->singleton(FilterManager::class, function ($app) {
            return new FilterManager($app);
        });

        // Register the filter drivers
        $this->app->bind('laravelfilter.driver.eloquent', function ($app) {
            return new EloquentDriver();
        });

        $this->app->bind('laravelfilter.driver.mongo', function ($app) {
            return new MongoDriver();
        });

        // Dynamically bind the default driver
        $this->app->bind(FilterDriverInterface::class, function ($app) {
            $defaultDriver = config('laravelfilter.default_driver');
            return $app->make("laravelfilter.driver.{$defaultDriver}");
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // Publish the config file
        $this->publishes([
            __DIR__.'/../config/laravelfilter.php' => config_path('laravelfilter.php'),
        ], 'config');

        // Publish the migration
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../database/migrations/create_filter_presets_table.php.stub' => database_path('migrations/'.date('Y_m_d_His', time()).'_create_filter_presets_table.php'),
            ], 'migrations');
        }

        // Register console commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                \RashadKhan\LaravelFilter\Console\Commands\MakeFilterCommand::class,
            ]);
        }
    }
}
