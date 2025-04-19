<?php

namespace RashadKhan\LaravelFilter;

use RashadKhan\LaravelFilter\Drivers\AbstractDriver;
use RashadKhan\LaravelFilter\Support\FilterCollection;
use RashadKhan\LaravelFilter\Contracts\FilterServiceInterface;
use RashadKhan\LaravelFilter\Exceptions\InvalidFilterException;

abstract class FilterService implements FilterServiceInterface
{
    /**
     * The driver used to apply filters.
     *
     * @var AbstractDriver
     */
    protected $driver;

    /**
     * The allowed filters with their operators.
     *
     * @var array
     */
    protected $allowedFilters = [];

    /**
     * The searchable fields.
     *
     * @var array
     */
    protected $searchableFields = [];

    /**
     * The default pagination size.
     *
     * @var int
     */
    protected $defaultPerPage = 15;

    /**
     * Create a new filter service instance.
     *
     * @param AbstractDriver|null $driver
     */
    public function __construct(?AbstractDriver $driver = null)
    {
        $this->driver = $driver ?? $this->resolveDefaultDriver();
    }

    /**
     * Resolve the default driver.
     *
     * @return AbstractDriver
     */
    protected function resolveDefaultDriver()
    {
        $driver = config('laravelfilter.default_driver');
        $driverClass = config("laravelfilter.available_drivers.{$driver}");
        return app($driverClass);
    }

    /**
     * Apply filters to the query.
     *
     * @param mixed $query
     * @param array $filterParams
     * @return mixed
     */
    public function apply($query, array $filterParams)
    {
        // Apply search if provided
        if (isset($filterParams['search'])) {
            $query = $this->driver->applySearch($query, $this->getSearchableFields(), $filterParams['search']);
        }

        // Apply filters if provided
        if (isset($filterParams['filters'])) {
            $query = $this->applyFilters($query, $filterParams['filters']);
        }

        // Apply sorting if provided
        if (isset($filterParams['sort'])) {
            $query = $this->applySort($query, $filterParams['sort']);
        }

        // Apply pagination if provided
        $perPage = $filterParams['per_page'] ?? $this->defaultPerPage;
        $page = $filterParams['page'] ?? 1;

        return $this->driver->applyPagination($query, $perPage, $page);
    }

    /**
     * Get the allowed filters.
     *
     * @return array
     */
    public function getAllowedFilters(): array
    {
        return $this->allowedFilters;
    }

    /**
     * Get the searchable fields.
     *
     * @return array
     */
    public function getSearchableFields(): array
    {
        return $this->searchableFields;
    }

    /**
     * Apply filters to the query.
     *
     * @param mixed $query
     * @param array $filters
     * @return mixed
     * @throws InvalidFilterException
     */
    protected function applyFilters($query, array $filters)
    {
        $logic = $filters['logic'] ?? 'and';
        $filterCollection = new FilterCollection($filters, $logic);

        if (!$filterCollection->hasFilters()) {
            return $query;
        }

        // Process logic type (AND/OR)
        $whereMethod = strtolower($logic) === 'or' ? 'orWhere' : 'where';

        return $query->$whereMethod(function ($q) use ($filterCollection) {
            $this->processFilterCollection($q, $filterCollection);
        });
    }

    /**
     * Process a filter collection and apply it to the query.
     *
     * @param mixed $query
     * @param FilterCollection $filterCollection
     * @return void
     * @throws InvalidFilterException
     */
    protected function processFilterCollection($query, FilterCollection $filterCollection)
    {
        $logic = $filterCollection->getLogic();
        $filters = $filterCollection->getFilters();

        foreach ($filters as $filter) {
            if ($filter['type'] === 'group') {
                // Process nested filter group using recursive call
                $whereMethod = strtolower($filter['logic']) === 'or' ? 'orWhere' : 'where';
                $query->$whereMethod(function ($q) use ($filter) {
                    $this->processFilterCollection($q, $filter['filters']);
                });
            } else {
                // Process individual condition
                if (!$this->isValidFilter($filter)) {
                    throw InvalidFilterException::invalidFilter($filter['field'], $filter['operator']);
                }

                $fieldType = $this->allowedFilters[$filter['field']]['type'];
                $whereMethod = strtolower($logic) === 'or' ? 'orWhere' : 'where';

                // Use dynamic method name based on logic
                if (method_exists($this->driver, 'apply' . ucfirst($whereMethod))) {
                    $method = 'apply' . ucfirst($whereMethod);
                    $this->driver->$method($query, $fieldType, $filter['field'], $filter['operator'], $filter['value']);
                } else {
                    // Fallback to standard applyWhere with logic context
                    $this->driver->applyWhere($query, $fieldType, $filter['field'], $filter['operator'], $filter['value'], $logic);
                }
            }
        }
    }

    /**
     * Check if a filter is valid.
     *
     * @param array $filter
     * @return bool
     */
    protected function isValidFilter(array $filter): bool
    {
        if ($filter['type'] === 'group') {
            return true;
        }

        return isset($this->allowedFilters[$filter['field']]['operators']) &&
            in_array($filter['operator'], $this->allowedFilters[$filter['field']]['operators']);
    }

    /**
     * Apply sorting to the query.
     *
     * @param mixed $query
     * @param array|string $sort
     * @return mixed
     */
    protected function applySort($query, $sort)
    {
        // Handle string format like "field:direction"
        if (is_string($sort)) {
            $parts = explode(':', $sort);
            $field = $parts[0];
            $direction = $parts[1] ?? 'asc';

            if (array_key_exists($field, $this->allowedFilters)) {
                return $this->driver->applyOrderBy($query, $field, $direction);
            }

            return $query;
        }

        // Handle array format like ["field" => "direction"]
        foreach ($sort as $field => $direction) {
            if (array_key_exists($field, $this->allowedFilters)) {
                $query = $this->driver->applyOrderBy($query, $field, $direction);
            }
        }

        return $query;
    }
}
