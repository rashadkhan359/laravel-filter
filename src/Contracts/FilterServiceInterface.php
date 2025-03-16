<?php

namespace RashadKhan\LaravelFilter\Contracts;

interface FilterServiceInterface
{
    /**
     * Apply filters to the query builder.
     *
     * @param mixed $query
     * @param array $filterParams
     * @return mixed
     */
    public function apply($query, array $filterParams);

    /**
     * Get the allowed filters for this service.
     *
     * @return array
     */
    public function getAllowedFilters(): array;

    /**
     * Get the searchable fields for this service.
     *
     * @return array
     */
    public function getSearchableFields(): array;
}
