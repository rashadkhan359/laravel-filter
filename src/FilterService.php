<?php

namespace RashadKhan\LaravelFilter;

use RashadKhan\LaravelFilter\Contracts\FilterServiceInterface;
use RashadKhan\LaravelFilter\Drivers\AbstractDriver;
use RashadKhan\LaravelFilter\Exceptions\InvalidFilterException;
use RashadKhan\LaravelFilter\Support\FilterCollection;

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
        $driverClass = config('query-filter.default_driver');
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
        $filterCollection = new FilterCollection($filters);

        foreach ($filterCollection->getFilters() as $filter) {
            if (!$this->isValidFilter($filter)) {
                throw InvalidFilterException::invalidFilter($filter['field'], $filter['operator']);
            }

            $query = $this->driver->applyWhere($query, $filter['field'], $filter['operator'], $filter['value']);
        }

        return $query;
    }

    /**
     * Check if a filter is valid.
     *
     * @param array $filter
     * @return bool
     */
    protected function isValidFilter(array $filter): bool
    {
        return isset($this->allowedFilters[$filter['field']]) &&
            in_array($filter['operator'], $this->allowedFilters[$filter['field']]);
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
