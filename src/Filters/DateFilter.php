<?php

namespace RashadKhan\LaravelFilter\Filters;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Exceptions\InvalidFormatException;

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
        $parsedValue = $this->parseDateValue($operator, $value);
        
        if (!$parsedValue) {
            return $query; // Skip the filter if value is invalid
        }

        switch ($operator) {
            case 'eq':
                return $query->whereDate($field, '=', $parsedValue);
            case 'neq':
                return $query->whereDate($field, '<>', $parsedValue);
            case 'gt':
                return $query->whereDate($field, '>', $parsedValue);
            case 'gte':
                return $query->whereDate($field, '>=', $parsedValue);
            case 'lt':
                return $query->whereDate($field, '<', $parsedValue);
            case 'lte':
                return $query->whereDate($field, '<=', $parsedValue);
            case 'between':
                return $query->whereBetween($field, $parsedValue);
            case 'not_between':
                return $query->whereNotBetween($field, $parsedValue);
            case 'in':
                return $query->whereIn($field, $parsedValue);
            case 'not_in':
                return $query->whereNotIn($field, $parsedValue);
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
                return $query->whereDate($field, '=', $parsedValue);
        }
    }


    /**
     * Parse the date value, handling various formats.
     * @param string $operator
     * @param mixed $value
     * @return string|array|boolean
     */
    private function parseDateValue(string $operator, $value)
    {
        // Handle null values
        if ($value === null && !$this->isNullValidCase($operator)) {
            return false;
        }

        // Handle array values (for between, in, etc.)
        if (is_array($value)) {
            $result = [];
            foreach ($value as $index => $item) {
                $parsed = $this->parseSingleDate($operator, $item);
                if (!$parsed) {
                    // For critical operations like between, we need both dates
                    // For in/not_in, we can filter out invalid dates
                    if (count($value) < 2) {
                        return false;
                    }
                } else {
                    $result[] = $parsed;
                }
            }
            return empty($result) ? false : $result;
        }

        // Handle single value
        return $this->parseSingleDate($operator, $value);
    }

    /**
     * Parse a single date value in various formats.
     *
     * @param mixed $value
     * @return string|null
     */
    private function parseSingleDate($operator, $value)
    {
        // Already a Carbon instance
        if ($value instanceof Carbon) {
            return $value->toDateString();
        }

        // Empty values
        if ($this->isNullValidCase($operator)) {
            return true;
        }

        if ((empty($value) || $value === 'null')) {
            return false;
        }

        // Try to parse the date
        try {
            // Common formats to try
            $formats = [
                'Y-m-d',        // 2023-02-23
                'Y-m-d H:i:s',  // 2023-02-23 14:00:00
                'd/m/Y',        // 23/02/2023
                'm/d/Y',        // 02/23/2023
                'd-m-Y',        // 23-02-2023
                'm-d-Y',        // 02-23-2023
                'd.m.Y',        // 23.02.2023
                'Y.m.d',        // 2023.02.23
                'M j, Y',       // Feb 23, 2023
                'j M Y',        // 23 Feb 2023
                'j F Y',        // 23 February 2023
            ];

            // Try each format
            foreach ($formats as $format) {
                try {
                    return Carbon::createFromFormat($format, $value)->toDateString();
                } catch (InvalidFormatException $e) {
                    // Continue to the next format
                    continue;
                }
            }

            // Last resort: try Carbon's flexible parsing
            return Carbon::parse($value)->toDateString();
        } catch (\Exception $e) {
            // Any parsing error means the date is invalid
            return false;
        }
    }

    public function isNullValidCase(string $operator)
    {
        $nullValidOperators = ['today', 'yesterday', 'this_week', 'last_week', 'this_month', 'last_month', 'this_year', 'last_year'];
        return in_array($operator, $nullValidOperators);
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
