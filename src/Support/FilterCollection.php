<?php

namespace RashadKhan\LaravelFilter\Support;

class FilterCollection
{
    /**
     * The collection of filters.
     *
     * @var array
     */
    protected $filters = [];

    /**
     * The logic operator (AND/OR) for combining filters.
     *
     * @var string
     */
    protected $logic = 'and';

    /**
     * Create a new filter collection instance.
     *
     * @param array $filters
     * @param string $logic
     */
    public function __construct(array $filters, string $logic = 'and')
    {
        $this->logic = strtolower($logic);
        $this->parseFilters($filters);
    }

    /**
     * Parse the filters from the input array.
     *
     * @param array $filters
     * @return void
     */
    protected function parseFilters(array $filters)
    {
        // Handle nested filter groups
        if (isset($filters['groups']) && is_array($filters['groups'])) {
            foreach ($filters['groups'] as $group) {
                $this->filters[] = [
                    'type' => 'group',
                    'logic' => $group['logic'] ?? 'and',
                    'filters' => new self($group['filters'], $group['logic'] ?? 'and')
                ];
            }
        }

        // Handle flat filter list
        if (isset($filters['conditions']) && is_array($filters['conditions'])) {
            foreach ($filters['conditions'] as $filter) {
                if (isset($filter['field'], $filter['operator'], $filter['value'])) {
                    $this->filters[] = [
                        'type' => 'condition',
                        'field' => $filter['field'],
                        'operator' => $filter['operator'],
                        'value' => $filter['value']
                    ];
                }
            }
        } else if (!isset($filters['groups'])) {
            // Legacy format support - each filter is a direct array
            foreach ($filters as $filter) {
                if (isset($filter['field'], $filter['operator'], $filter['value'])) {
                    $this->filters[] = [
                        'type' => 'condition',
                        'field' => $filter['field'],
                        'operator' => $filter['operator'],
                        'value' => $filter['value']
                    ];
                }
            }
        }
    }

    /**
     * Get the filters.
     *
     * @return array
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    /**
     * Get the logic operator.
     *
     * @return string
     */
    public function getLogic(): string
    {
        return $this->logic;
    }

    /**
     * Check if the collection has any filters.
     *
     * @return bool
     */
    public function hasFilters(): bool
    {
        return !empty($this->filters);
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
