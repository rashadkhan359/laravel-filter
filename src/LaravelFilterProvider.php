<?php

namespace RashadKhan\LaravelFilter;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Livewire\Livewire;
use RashadKhan\LaravelFilter\Livewire\FilterBuilder as LivewireFilterBuilder;
use RashadKhan\LaravelFilter\Drivers\MongoDriver;
use RashadKhan\LaravelFilter\Drivers\EloquentDriver;
use RashadKhan\LaravelFilter\Contracts\FilterDriverInterface;

class LaravelFilterProvider extends ServiceProvider
{
    /**
     * Register any application services.
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

        // Register the FilterService binding
        $this->app->bind('filter', function ($app) {
            return new FilterManager();
        });
    }

    /**
     * Bootstrap any application services.
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

        // Views publishing
        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/laravel-filter'),
        ], 'views');

        // Assets publishing
        $this->publishes([
            __DIR__.'/../resources/js' => resource_path('js/vendor/laravel-filter'),
        ], 'assets');

        // Load views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'laravel-filter');

        // Register Livewire components if Livewire is installed
        if (class_exists(Livewire::class)) {
            Livewire::component('filter-builder', LivewireFilterBuilder::class);
        }

        // Register Alpine directive if Alpine.js is being used
        if (class_exists(Blade::class)) {
            Blade::directive('filterScripts', function () {
                return "<?php echo '<script src=\"'.asset('vendor/laravel-filter/alpine/filter-builder.js').'\"></script>'; ?>";
            });
        }
    }
}
