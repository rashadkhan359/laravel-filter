<?php

namespace RashadKhan\LaravelFilter\Filters;

use Illuminate\Database\Eloquent\Builder;

class TextFilter extends AbstractFilter
{
    /**
     * Apply the filter to the query.
     *
     * @param Builder $query
     * @param string $field
     * @param string $operator
     * @param mixed $value
     * @return Builder
     */
    public function apply($query, $field, $operator, $value)
    {
        switch ($operator) {
            case 'eq':
                return $query->where($field, '=', $value);
            case 'neq':
                return $query->where($field, '<>', $value);
            case 'contains':
                return $query->where($field, 'like', '%' . $value . '%');
            case 'starts_with':
                return $query->where($field, 'like', $value . '%');
            case 'ends_with':
                return $query->where($field, 'like', '%' . $value);
            case 'in':
                return $query->whereIn($field, $value);
            case 'not_in':
                return $query->whereNotIn($field, $value);
            default:
                return $query->where($field, '=', $value);
        }
    }

    /**
     * Create an equals filter condition.
     *
     * @param string $field
     * @param string $value
     * @return array
     */
    public static function eq(string $field, string $value): array
    {
        return self::conditions($field, 'eq', $value);
    }

    /**
     * Create a not equals filter condition.
     *
     * @param string $field
     * @param string $value
     * @return array
     */
    public static function neq(string $field, string $value): array
    {
        return self::conditions($field, 'neq', $value);
    }

    /**
     * Create a contains filter condition.
     *
     * @param string $field
     * @param string $value
     * @return array
     */
    public static function contains(string $field, string $value): array
    {
        return self::conditions($field, 'contains', $value);
    }

    /**
     * Create a starts with filter condition.
     *
     * @param string $field
     * @param string $value
     * @return array
     */
    public static function startsWith(string $field, string $value): array
    {
        return self::conditions($field, 'starts_with', $value);
    }

    /**
     * Create an ends with filter condition.
     *
     * @param string $field
     * @param string $value
     * @return array
     */
    public static function endsWith(string $field, string $value): array
    {
        return self::conditions($field, 'ends_with', $value);
    }

    /**
     * Create an in filter condition.
     *
     * @param string $field
     * @param array $values
     * @return array
     */
    public static function in(string $field, array $values): array
    {
        return self::conditions($field, 'in', $values);
    }

    /**
     * Create a not in filter condition.
     *
     * @param string $field
     * @param array $values
     * @return array
     */
    public static function notIn(string $field, array $values): array
    {
        return self::conditions($field, 'not_in', $values);
    }
}
