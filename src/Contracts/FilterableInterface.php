<?php

namespace RashadKhan\LaravelFilter\Contracts;

interface FilterableInterface
{
    /**
     * Get the filter service class for this model.
     *
     * @return string
     */
    public function getFilterServiceClass(): string;

    /**
     * Apply filters to the model's query.
     *
     * @param array $filterParams
     * @param mixed|null $query
     * @return mixed
     */
    public function filter(array $filterParams, $query = null);
}
