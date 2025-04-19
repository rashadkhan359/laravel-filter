<?php

namespace RashadKhan\LaravelFilter\Traits;

use Illuminate\Database\Eloquent\Builder;

trait Filterable
{
    /**
     * Apply filters to the model's query.
     *
     * @param Builder|null $query
     * @param array|null $filterParams
     * @return Builder
     */
    public function scopeFilter(Builder $query, ?array $filterParams = null)
    {
        // If no filter parameters provided, return unmodified query
        if ($filterParams === null) {
            return $query;
        }

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
        return $this->scopeFilter($query, $filterParams);
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
