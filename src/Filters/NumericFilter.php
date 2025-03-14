<?php

namespace RashadKhan\LaravelFilter\Filters;

use Illuminate\Database\Eloquent\Builder;

class NumericFilter extends AbstractFilter
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
            case 'gt':
                return $query->where($field, '>', $value);
            case 'gte':
                return $query->where($field, '>=', $value);
            case 'lt':
                return $query->where($field, '<', $value);
            case 'lte':
                return $query->where($field, '<=', $value);
            case 'between':
                return $query->whereBetween($field, $value);
            case 'not_between':
                return $query->whereNotBetween($field, $value);
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
     * @param mixed $value
     * @return array
     */
    public static function eq(string $field, $value): array
    {
        return self::conditions($field, 'eq', $value);
    }

    /**
     * Create a not equals filter condition.
     *
     * @param string $field
     * @param mixed $value
     * @return array
     */
    public static function neq(string $field, $value): array
    {
        return self::conditions($field, 'neq', $value);
    }

    /**
     * Create a greater than filter condition.
     *
     * @param string $field
     * @param mixed $value
     * @return array
     */
    public static function gt(string $field, $value): array
    {
        return self::conditions($field, 'gt', $value);
    }

    /**
     * Create a greater than or equal filter condition.
     *
     * @param string $field
     * @param mixed $value
     * @return array
     */
    public static function gte(string $field, $value): array
    {
        return self::conditions($field, 'gte', $value);
    }

    /**
     * Create a less than filter condition.
     *
     * @param string $field
     * @param mixed $value
     * @return array
     */
    public static function lt(string $field, $value): array
    {
        return self::conditions($field, 'lt', $value);
    }

    /**
     * Create a less than or equal filter condition.
     *
     * @param string $field
     * @param mixed $value
     * @return array
     */
    public static function lte(string $field, $value): array
    {
        return self::conditions($field, 'lte', $value);
    }

    /**
     * Create a between filter condition.
     *
     * @param string $field
     * @param array $values
     * @return array
     */
    public static function between(string $field, array $values): array
    {
        return self::conditions($field, 'between', $values);
    }

    /**
     * Create a not between filter condition.
     *
     * @param string $field
     * @param array $values
     * @return array
     */
    public static function notBetween(string $field, array $values): array
    {
        return self::conditions($field, 'not_between', $values);
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
