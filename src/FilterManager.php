<?php

namespace RashadKhan\LaravelFilter;

use Illuminate\Contracts\Container\Container;
use RashadKhan\LaravelFilter\Contracts\FilterDriverInterface;

class FilterManager
{
    /**
     * The container instance.
     *
     * @var Container
     */
    protected $app;

    /**
     * The active driver instances.
     *
     * @var array
     */
    protected $drivers = [];

    /**
     * Custom driver creators.
     *
     * @var array
     */
    protected $customCreators = [];

    /**
     * Custom adapter creators.
     *
     * @var array
     */
    protected $customAdapters = [];

    /**
     * Create a new filter manager instance.
     *
     * @param Container $app
     */
    public function __construct(Container $app)
    {
        $this->app = $app;
    }

    /**
     * Get a driver instance.
     *
     * @param string|null $driver
     * @return FilterDriverInterface
     */
    public function driver(?string $driver = null): FilterDriverInterface
    {
        $driver = $driver ?: $this->getDefaultDriver();

        if (!isset($this->drivers[$driver])) {
            $this->drivers[$driver] = $this->createDriver($driver);

            // Set the appropriate adapter for this driver
            $this->setAdapterForDriver($this->drivers[$driver], $driver);
        }

        return $this->drivers[$driver];
    }

    /**
     * Create a new driver instance.
     *
     * @param string $driver
     * @return FilterDriverInterface
     *
     * @throws \InvalidArgumentException
     */
    protected function createDriver(string $driver): FilterDriverInterface
    {
        if (isset($this->customCreators[$driver])) {
            $driverInstance = $this->callCustomCreator($driver);
            if ($driverInstance instanceof FilterDriverInterface) {
                $driverInstance->setName($driver);
                return $driverInstance;
            }
        }

        // Next check for a registered driver binding in the container
        $driverBindingKey = "laravelfilter.driver.{$driver}";
        if ($this->app->bound($driverBindingKey)) {
            $driverInstance = $this->app->make($driverBindingKey);
            if ($driverInstance instanceof FilterDriverInterface) {
                $driverInstance->setName($driver);
                return $driverInstance;
            }
        }

        // Finally, try to resolve from the available drivers config
        $driverClass = config("laravelfilter.available_drivers.{$driver}");

        if (!$driverClass || !class_exists($driverClass)) {
            throw new \InvalidArgumentException("Driver [{$driver}] not supported.");
        }

        $driverInstance = $this->app->make($driverClass);

        if (!$driverInstance instanceof FilterDriverInterface) {
            throw new \InvalidArgumentException("Driver [{$driver}] must implement FilterDriverInterface.");
        }

        $driverInstance->setName($driver);
        return $driverInstance;
    }

    /**
     * Set the appropriate adapter for a driver instance.
     *
     * @param FilterDriverInterface $driver
     * @param string $driverName
     * @return void
     */
    protected function setAdapterForDriver(FilterDriverInterface $driver, string $driverName): void
    {
        // Check if there's a custom adapter for this driver
        if (isset($this->customAdapters[$driverName])) {
            $adapter = $this->callCustomAdapterCreator($driverName);
            $driver->setAdapter($adapter);
            return;
        }

        // Check if there's a configured adapter for this driver
        $adapterClass = config("laravelfilter.driver_adapters.{$driverName}");

        if ($adapterClass && class_exists($adapterClass)) {
            $adapter = $this->app->make($adapterClass);
            $driver->setAdapter($adapter);
            return;
        }

        // Check if there's a default adapter binding
        $adapterBindingKey = "laravelfilter.adapter.{$driverName}";
        if ($this->app->bound($adapterBindingKey)) {
            $adapter = $this->app->make($adapterBindingKey);
            $driver->setAdapter($adapter);
            return;
        }

        // Finally, use a fallback adapter if defined
        $fallbackAdapterClass = config('laravelfilter.fallback_adapter');
        if ($fallbackAdapterClass && class_exists($fallbackAdapterClass)) {
            $adapter = $this->app->make($fallbackAdapterClass);
            $driver->setAdapter($adapter);
        }
    }

    /**
     * Get the default driver name.
     *
     * @return string
     */
    public function getDefaultDriver(): string
    {
        return config('laravelfilter.default_driver', 'eloquent');
    }

    /**
     * Register a custom driver creator.
     *
     * @param string $driver
     * @param callable $callback
     * @return $this
     */
    public function extend(string $driver, callable $callback): self
    {
        $this->customCreators[$driver] = $callback;
        return $this;
    }


    /**
     * Register a custom adapter for a driver.
     *
     * @param string $driver
     * @param callable $callback
     * @return $this
     */
    public function withAdapter(string $driver, callable $callback): self
    {
        $this->customAdapters[$driver] = $callback;
        return $this;
    }

    /**
     * Call a custom driver creator.
     *
     * @param string $driver
     * @return FilterDriverInterface
     */
    protected function callCustomCreator(string $driver): FilterDriverInterface
    {
        return $this->customCreators[$driver]($this->app);
    }

    /**
     * Call a custom adapter creator.
     *
     * @param string $driver
     * @return mixed
     */
    protected function callCustomAdapterCreator(string $driver)
    {
        return $this->customAdapters[$driver]($this->app);
    }

    /**
     * Get all available driver names.
     *
     * @return array
     */
    public function getAvailableDrivers(): array
    {
        $configDrivers = array_keys(config('laravelfilter.available_drivers', []));
        $customDrivers = array_keys($this->customCreators);

        return array_unique(array_merge($configDrivers, $customDrivers));
    }

    /**
     * Automatically detect the appropriate driver based on the current connection.
     *
     * @return FilterDriverInterface
     */
    public function detectDriver(): FilterDriverInterface
    {
        $connection = $this->app['db']->connection()->getDriverName();

        // Map database connection types to driver types
        $driverMap = config('laravelfilter.connection_driver_map', [
            'mysql' => 'eloquent',
            'sqlite' => 'eloquent',
            'pgsql' => 'eloquent',
            'sqlsrv' => 'eloquent',
            'mongodb' => 'mongo',
        ]);

        $driver = $driverMap[$connection] ?? $this->getDefaultDriver();

        return $this->driver($driver);
    }
}
