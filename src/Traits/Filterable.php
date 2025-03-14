<?php

namespace RashadKhan\LaravelFilter\Traits;

use Illuminate\Database\Eloquent\Builder;
use RashadKhan\LaravelFilter\Contracts\FilterServiceInterface;
use RashadKhan\LaravelFilter\FilterManager;

trait Filterable
{
    /**
     * Apply filters to the model's query.
     *
     * @param array $filterParams
     * @param Builder|null $query
     * @return mixed
     */
    public function filter(array $filterParams, $query = null)
    {
        $query = $query ?? $this->newQuery();
        $filterClass = $this->getFilterServiceClass();

        // Create filter service instance
        $filterService = app($filterClass);

        // Apply filters and return result
        return $filterService->apply($query, $filterParams);
    }

    /**
     * Get filter service class for this model.
     *
     * @return string
     */
    public function getFilterServiceClass(): string
    {
        if (property_exists($this, 'filterServiceClass')) {
            return $this->filterServiceClass;
        }

        $modelClass = get_class($this);
        $modelName = class_basename($modelClass);

        return "App\\Filters\\" . $modelName . "Filter";
    }

    /**
     * Scope a query to apply filters.
     *
     * @param Builder $query
     * @param array $filterParams
     * @return Builder
     */
    public function scopeWithFilters(Builder $query, array $filterParams)
    {
        return $this->filter($filterParams, $query);
    }

    /**
     * Get the available filters for this model.
     *
     * @return array
     */
    public function getAvailableFilters(): array
    {
        $filterClass = $this->getFilterServiceClass();
        $filterService = app($filterClass);

        return $filterService->getAllowedFilters();
    }

    /**
     * Get the searchable fields for this model.
     *
     * @return array
     */
    public function getSearchableFields(): array
    {
        $filterClass = $this->getFilterServiceClass();
        $filterService = app($filterClass);

        return $filterService->getSearchableFields();
    }
}
