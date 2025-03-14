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

    protected $customCreators;

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
            return $this->callCustomCreator($driver);
        }

        $driverClass = $this->getDriverClass($driver);

        if (!class_exists($driverClass)) {
            throw new \InvalidArgumentException("Driver [{$driver}] not supported.");
        }

        return $this->app->make($driverClass);
    }

    /**
     * Get the driver class name.
     *
     * @param string $driver
     * @return string
     */
    protected function getDriverClass(string $driver): string
    {
        $drivers = $this->getDrivers();

        return $drivers[$driver] ?? '';
    }

    /**
     * Get the default driver name.
     *
     * @return string
     */
    public function getDefaultDriver(): string
    {
        return config('laravelfilter.default_driver');
    }

    /**
     * Register a custom driver creator.
     *
     * @param string $driver
     * @param \Closure $callback
     * @return $this
     */
    public function extend(string $driver, \Closure $callback): self
    {
        $this->customCreators[$driver] = $callback;
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
     * Get all registered drivers.
     *
     * @return array
     */
    public function getDrivers(): array
    {
        return array_keys(config('laravelfilter.available_drivers'));
    }
}
