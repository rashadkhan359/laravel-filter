<?php

namespace RashadKhan\LaravelFilter\Filters;

class CustomFilter extends AbstractFilter
{
    /**
     * Static helper for JSON contains key condition.
     *
     * @param string $field
     * @param string $key
     * @return array
     */
    public static function jsonContainsKey(string $field, string $key): array
    {
        return self::conditions($field, 'json_contains_key', $key);
    }

    /**
     * Static helper for JSON contains value condition.
     *
     * @param string $field
     * @param mixed $value
     * @return array
     */
    public static function jsonContainsValue(string $field, $value): array
    {
        return self::conditions($field, 'json_contains_value', $value);
    }

    /**
     * Static helper for JSON path condition.
     *
     * @param string $field
     * @param string $path
     * @param mixed $value
     * @return array
     */
    public static function jsonPath(string $field, string $path, $value): array
    {
        return self::conditions($field, 'json_path', [
            'path' => $path,
            'value' => $value
        ]);
    }

    /**
     * Get the conditions.
     *
     * @param string $field
     * @param string $operator
     * @param mixed $value
     * @return array
     */
    public static function conditions(string $field, string $operator, $value): array
    {
        $conditions = parent::conditions($field, $operator, $value);
        $conditions['type'] = 'condition';
        return $conditions;
    }

    /**
     * Apply JSON contains key condition.
     *
     * @param mixed $query
     * @param string $field
     * @param mixed $value
     * @param string $whereMethod
     * @return mixed
     */
    protected function applyJsonContainsKey($query, string $field, $value, string $whereMethod)
    {
        if ($whereMethod === 'orWhere') {
            return $query->orWhereJsonContains($field, $value);
        }

        return $query->whereJsonContains($field, $value);
    }

    /**
     * Apply JSON contains value condition.
     *
     * @param mixed $query
     * @param string $field
     * @param mixed $value
     * @param string $whereMethod
     * @return mixed
     */
    protected function applyJsonContainsValue($query, string $field, $value, string $whereMethod)
    {
        if ($whereMethod === 'orWhere') {
            return $query->orWhereJsonContains($field, $value);
        }

        return $query->whereJsonContains($field, $value);
    }

    /**
     * Apply JSON path condition.
     *
     * @param mixed $query
     * @param string $field
     * @param mixed $value
     * @param string $whereMethod
     * @return mixed
     */
    protected function applyJsonPath($query, string $field, $value, string $whereMethod)
    {
        $path = $value['path'] ?? '';
        $pathValue = $value['value'] ?? null;

        if (empty($path)) {
            return $query;
        }

        $jsonPath = $field . '->' . str_replace('.', '->', $path);

        if ($whereMethod === 'orWhere') {
            return $query->orWhere($jsonPath, '=', $pathValue);
        }

        return $query->where($jsonPath, '=', $pathValue);
    }
}
