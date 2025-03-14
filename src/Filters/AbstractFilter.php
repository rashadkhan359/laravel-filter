<?php

namespace RashadKhan\LaravelFilter\Filters;

abstract class AbstractFilter
{
    /**
     * Apply the filter to the query.
     *
     * @param mixed $query
     * @param mixed $field
     * @param mixed $operator
     * @param mixed $value
     * @return mixed
     */
    abstract public function apply($query, $field, $operator, $value);

    /**
     * Get the filter conditions.
     *
     * @param string $field
     * @param string $operator
     * @param mixed $value
     * @return array
     */
    public static function conditions(string $field, string $operator, $value): array
    {
        return [
            'field' => $field,
            'operator' => $operator,
            'value' => $value
        ];
    }
}
