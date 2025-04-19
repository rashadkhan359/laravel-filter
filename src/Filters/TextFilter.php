<?php

namespace RashadKhan\LaravelFilter\Filters;

use Illuminate\Database\Eloquent\Builder;

class TextFilter extends AbstractFilter
{
    /**
     * Static helper for equals condition.
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
     * Static helper for not equals condition.
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
     * Static helper for contains condition.
     *
     * @param string $field
     * @param mixed $value
     * @return array
     */
    public static function contains(string $field, $value): array
    {
        return self::conditions($field, 'contains', $value);
    }

    /**
     * Static helper for starts with condition.
     *
     * @param string $field
     * @param mixed $value
     * @return array
     */
    public static function startsWith(string $field, $value): array
    {
        return self::conditions($field, 'starts_with', $value);
    }

    /**
     * Static helper for ends with condition.
     *
     * @param string $field
     * @param mixed $value
     * @return array
     */
    public static function endsWith(string $field, $value): array
    {
        return self::conditions($field, 'ends_with', $value);
    }

    /**
     * Static helper for in condition.
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
     * Static helper for not in condition.
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
     * Static helper for is null condition.
     *
     * @param string $field
     * @return array
     */
    public static function isNull(string $field): array
    {
        return self::conditions($field, 'is_null', null);
    }

    /**
     * Static helper for is not null condition.
     *
     * @param string $field
     * @return array
     */
    public static function isNotNull(string $field): array
    {
        return self::conditions($field, 'is_not_null', null);
    }

    /**
     * Helper to create filter conditions.
     *
     * @param string $field
     * @param string $operator
     * @param mixed $value
     * @return array
     */
    public static function conditions(string $field, string $operator, $value): array
    {
        // Call parent implementation to ensure consistency
        $conditions = parent::conditions($field, $operator, $value);

        // Add the type field for new format support
        $conditions['type'] = 'condition';

        return $conditions;
    }

    /**
     * Apply equals operator.
     *
     * @param mixed $query
     * @param string $field
     * @param mixed $value
     * @param string $whereMethod
     * @return mixed
     */
    protected function applyEq($query, string $field, $value, string $whereMethod)
    {
        return $this->applyWhereWithMethod($query, $field, $value, $whereMethod, '=');
    }

    /**
     * Apply not equals operator.
     *
     * @param mixed $query
     * @param string $field
     * @param mixed $value
     * @param string $whereMethod
     * @return mixed
     */
    protected function applyNeq($query, string $field, $value, string $whereMethod)
    {
        return $this->applyWhereWithMethod($query, $field, $value, $whereMethod, '!=');
    }

    /**
     * Apply contains operator.
     *
     * @param mixed $query
     * @param string $field
     * @param mixed $value
     * @param string $whereMethod
     * @return mixed
     */
    protected function applyContains($query, string $field, $value, string $whereMethod)
    {
        return $this->applyWhereWithMethod($query, $field, '%' . $value . '%', $whereMethod, 'LIKE');
    }

    /**
     * Apply starts with operator.
     *
     * @param mixed $query
     * @param string $field
     * @param mixed $value
     * @param string $whereMethod
     * @return mixed
     */
    protected function applyStartsWith($query, string $field, $value, string $whereMethod)
    {
        return $this->applyWhereWithMethod($query, $field, $value . '%', $whereMethod, 'LIKE');
    }

    /**
     * Apply ends with operator.
     *
     * @param mixed $query
     * @param string $field
     * @param mixed $value
     * @param string $whereMethod
     * @return mixed
     */
    protected function applyEndsWith($query, string $field, $value, string $whereMethod)
    {
        return $this->applyWhereWithMethod($query, $field, '%' . $value, $whereMethod, 'LIKE');
    }

    /**
     * Apply in operator.
     *
     * @param mixed $query
     * @param string $field
     * @param mixed $value
     * @param string $whereMethod
     * @return mixed
     */
    protected function applyIn($query, string $field, $value, string $whereMethod)
    {
        $method = $whereMethod . 'In';
        return $query->$method($field, is_array($value) ? $value : [$value]);
    }

    /**
     * Apply not in operator.
     *
     * @param mixed $query
     * @param string $field
     * @param mixed $value
     * @param string $whereMethod
     * @return mixed
     */
    protected function applyNotIn($query, string $field, $value, string $whereMethod)
    {
        $method = $whereMethod . 'NotIn';
        return $query->$method($field, is_array($value) ? $value : [$value]);
    }

    /**
     * Apply is null operator.
     *
     * @param mixed $query
     * @param string $field
     * @param mixed $value
     * @param string $whereMethod
     * @return mixed
     */
    protected function applyIsNull($query, string $field, $value, string $whereMethod)
    {
        return $this->applyWhereNull($query, $field, $whereMethod, true);
    }

    /**
     * Apply is not null operator.
     *
     * @param mixed $query
     * @param string $field
     * @param mixed $value
     * @param string $whereMethod
     * @return mixed
     */
    protected function applyIsNotNull($query, string $field, $value, string $whereMethod)
    {
        return $this->applyWhereNull($query, $field, $whereMethod, false);
    }
}
