<?php

namespace RashadKhan\LaravelFilter\Support;

class FilterCollection
{
    /**
     * The filters array.
     *
     * @var array
     */
    protected $filters = [];

    /**
     * Create a new filter collection instance.
     *
     * @param array $filters
     */
    public function __construct(array $filters = [])
    {
        $this->parseFilters($filters);
    }

    /**
     * Parse the filters.
     *
     * @param array $filters
     * @return void
     */
    protected function parseFilters(array $filters): void
    {
        foreach ($filters as $key => $value) {
            // Handle associative array format (field => value)
            if (is_string($key) && !is_array($value)) {
                $this->filters[] = [
                    'field' => $key,
                    'operator' => 'eq',
                    'value' => $value
                ];
            }
            // Handle nested array format with operator
            elseif (is_string($key) && is_array($value) && isset($value['operator'])) {
                $this->filters[] = [
                    'field' => $key,
                    'operator' => $value['operator'],
                    'value' => $value['value'] ?? null
                ];
            }
            // Handle pre-formatted filter array
            elseif (is_array($value) && isset($value['field']) && isset($value['operator'])) {
                $this->filters[] = $value;
            }
        }
    }

    /**
     * Get all filters.
     *
     * @return array
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    /**
     * Add a filter.
     *
     * @param array $filter
     * @return $this
     */
    public function add(array $filter)
    {
        $this->filters[] = $filter;
        return $this;
    }

    /**
     * Remove filters by field.
     *
     * @param string $field
     * @return $this
     */
    public function removeByField(string $field)
    {
        $this->filters = array_filter($this->filters, function ($filter) use ($field) {
            return $filter['field'] !== $field;
        });

        return $this;
    }

    /**
     * Check if collection has filters.
     *
     * @return bool
     */
    public function hasFilters(): bool
    {
        return !empty($this->filters);
    }

    /**
     * Get count of filters.
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->filters);
    }

    /**
     * Convert to array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->filters;
    }
}
