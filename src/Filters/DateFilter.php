<?php

namespace RashadKhan\LaravelFilter\Filters;

use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class DateFilter extends AbstractFilter
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
                return $query->whereDate($field, '=', $value);
            case 'neq':
                return $query->whereDate($field, '<>', $value);
            case 'gt':
                return $query->whereDate($field, '>', $value);
            case 'gte':
                return $query->whereDate($field, '>=', $value);
            case 'lt':
                return $query->whereDate($field, '<', $value);
            case 'lte':
                return $query->whereDate($field, '<=', $value);
            case 'between':
                return $query->whereBetween($field, $value);
            case 'not_between':
                return $query->whereNotBetween($field, $value);
            case 'in':
                return $query->whereIn($field, $value);
            case 'not_in':
                return $query->whereNotIn($field, $value);
            case 'today':
                return $query->whereDate($field, '=', Carbon::today());
            case 'yesterday':
                return $query->whereDate($field, '=', Carbon::yesterday());
            case 'this_week':
                return $query->whereBetween($field, [
                    Carbon::now()->startOfWeek(),
                    Carbon::now()->endOfWeek()
                ]);
            case 'last_week':
                return $query->whereBetween($field, [
                    Carbon::now()->subWeek()->startOfWeek(),
                    Carbon::now()->subWeek()->endOfWeek()
                ]);
            case 'this_month':
                return $query->whereBetween($field, [
                    Carbon::now()->startOfMonth(),
                    Carbon::now()->endOfMonth()
                ]);
            case 'last_month':
                return $query->whereBetween($field, [
                    Carbon::now()->subMonth()->startOfMonth(),
                    Carbon::now()->subMonth()->endOfMonth()
                ]);
            case 'this_year':
                return $query->whereBetween($field, [
                    Carbon::now()->startOfYear(),
                    Carbon::now()->endOfYear()
                ]);
            case 'last_year':
                return $query->whereBetween($field, [
                    Carbon::now()->subYear()->startOfYear(),
                    Carbon::now()->subYear()->endOfYear()
                ]);
            default:
                return $query->whereDate($field, '=', $value);
        }
    }

    /**
     * Create an equals date filter condition.
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
     * Create a not equals date filter condition.
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
     * Create a greater than date filter condition.
     *
     * @param string $field
     * @param string $value
     * @return array
     */
    public static function gt(string $field, string $value): array
    {
        return self::conditions($field, 'gt', $value);
    }

    /**
     * Create a greater than or equal date filter condition.
     *
     * @param string $field
     * @param string $value
     * @return array
     */
    public static function gte(string $field, string $value): array
    {
        return self::conditions($field, 'gte', $value);
    }

    /**
     * Create a less than date filter condition.
     *
     * @param string $field
     * @param string $value
     * @return array
     */
    public static function lt(string $field, string $value): array
    {
        return self::conditions($field, 'lt', $value);
    }

    /**
     * Create a less than or equal date filter condition.
     *
     * @param string $field
     * @param string $value
     * @return array
     */
    public static function lte(string $field, string $value): array
    {
        return self::conditions($field, 'lte', $value);
    }

    /**
     * Create a between date filter condition.
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
     * Create a not between date filter condition.
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
     * Create an in date filter condition.
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
     * Create a not in date filter condition.
     *
     * @param string $field
     * @param array $values
     * @return array
     */
    public static function notIn(string $field, array $values): array
    {
        return self::conditions($field, 'not_in', $values);
    }

    /**
     * Create a today filter condition.
     *
     * @param string $field
     * @return array
     */
    public static function today(string $field): array
    {
        return self::conditions($field, 'today', null);
    }

    /**
     * Create a yesterday filter condition.
     *
     * @param string $field
     * @return array
     */
    public static function yesterday(string $field): array
    {
        return self::conditions($field, 'yesterday', null);
    }

    /**
     * Create a this week filter condition.
     *
     * @param string $field
     * @return array
     */
    public static function thisWeek(string $field): array
    {
        return self::conditions($field, 'this_week', null);
    }

    /**
     * Create a last week filter condition.
     *
     * @param string $field
     * @return array
     */
    public static function lastWeek(string $field): array
    {
        return self::conditions($field, 'last_week', null);
    }

    /**
     * Create a this month filter condition.
     *
     * @param string $field
     * @return array
     */
    public static function thisMonth(string $field): array
    {
        return self::conditions($field, 'this_month', null);
    }

    /**
     * Create a last month filter condition.
     *
     * @param string $field
     * @return array
     */
    public static function lastMonth(string $field): array
    {
        return self::conditions($field, 'last_month', null);
    }

    /**
     * Create a this year filter condition.
     *
     * @param string $field
     * @return array
     */
    public static function thisYear(string $field): array
    {
        return self::conditions($field, 'this_year', null);
    }

    /**
     * Create a last year filter condition.
     *
     * @param string $field
     * @return array
     */
    public static function lastYear(string $field): array
    {
        return self::conditions($field, 'last_year', null);
    }
}
