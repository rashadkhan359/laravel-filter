<?php

namespace RashadKhan\LaravelFilter\Filters;

use Illuminate\Database\Eloquent\Builder;

class RelationshipFilter extends AbstractFilter
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
        if ($operator === 'relation') {
            list($relation, $relationField, $relationOperator, $relationValue) = $value;
            return $query->whereHas($relation, function ($q) use ($relationField, $relationOperator, $relationValue) {
                // Get the appropriate filter for this field type
                $filter = $this->resolveFilterForField($relationField);
                return $filter->apply($q, $relationField, $relationOperator, $relationValue);
            });
        } elseif ($operator === 'relationCount') {
            list($countOperator, $count) = $value;
            return $query->has($field, $countOperator, $count);
        }

        return $query;
    }

    /**
     * Resolve the appropriate filter for a field based on its type.
     *
     * @param string $field
     * @return AbstractFilter
     */
    protected function resolveFilterForField(string $field)
    {
        // This should be implemented based on your field type detection logic
        // For now, we'll return a generic TextFilter as default
        return new TextFilter();
    }

    /**
     * Define relationship filter conditions.
     *
     * @param string $relation
     * @param string $field
     * @param string $operator
     * @param mixed $value
     * @return array
     */
    public static function withCondition(string $relation, string $field, string $operator, $value): array
    {
        return self::conditions($relation, 'relation', [$relation, $field, $operator, $value]);
    }

    /**
     * Define a count filter for a relationship.
     *
     * @param string $relation
     * @param string $operator
     * @param int $count
     * @return array
     */
    public static function withCount(string $relation, string $operator, int $count): array
    {
        return self::conditions($relation, 'relationCount', [$operator, $count]);
    }
}
