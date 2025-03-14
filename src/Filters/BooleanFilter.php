<?php

namespace RashadKhan\LaravelFilter\Filters;

use Illuminate\Database\Eloquent\Builder;

class BooleanFilter extends AbstractFilter
{
    /**
     * Apply the filter to the query.
     *
     * @param Builder $query
     * @param string $field
     * @param string $operator
     * @param bool|string $value
     * @return Builder
     */
    public function apply($query, $field, $operator, $value)
    {
        // Convert string values to boolean
        if (is_string($value)) {
            $value = strtolower($value) === 'true' || $value === '1';
        }

        return $query->where($field, $value);
    }

    /**
     * Create a boolean filter condition.
     *
     * @param string $field
     * @param bool|string $value
     * @return array
     */
    public static function is(string $field, $value): array
    {
        return self::conditions($field, 'boolean', $value);
    }
}
