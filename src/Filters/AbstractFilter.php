<?php

namespace RashadKhan\LaravelFilter\Filters;

abstract class AbstractFilter
{
    /**
     * Apply the filter to the query.
     *
     * @param mixed $query
     * @param string $field
     * @param string $operator
     * @param mixed $value
     * @param string $logic
     * @return mixed
     */
    public function apply($query, string $field, string $operator, $value, string $logic = 'and')
    {
        $method = $this->getMethodForOperator($operator);

        if (!method_exists($this, $method)) {
            return $query;
        }

        // Handle WHERE/ORWHERE based on logic
        $whereMethod = strtolower($logic) === 'or' ? 'orWhere' : 'where';

        // Apply the filter with the appropriate where method
        return $this->$method($query, $field, $value, $whereMethod);
    }

    /**
     * Get the method name for the operator.
     *
     * @param string $operator
     * @return string
     */
    protected function getMethodForOperator(string $operator): string
    {
        return 'apply' . str_replace('_', '', ucwords($operator, '_'));
    }

    /**
     * Apply a basic where clause with dynamic method.
     *
     * @param mixed $query
     * @param string $field
     * @param mixed $value
     * @param string $whereMethod
     * @param string $operator
     * @return mixed
     */
    protected function applyWhereWithMethod($query, string $field, $value, string $whereMethod, string $operator)
    {
        return $query->$whereMethod($field, $operator, $value);
    }

    /**
     * Apply a null check with dynamic method.
     *
     * @param mixed $query
     * @param string $field
     * @param string $whereMethod
     * @param bool $isNull
     * @return mixed
     */
    protected function applyWhereNull($query, string $field, string $whereMethod, bool $isNull = true)
    {
        $method = $whereMethod . ($isNull ? 'Null' : 'NotNull');
        return $query->$method($field);
    }

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
