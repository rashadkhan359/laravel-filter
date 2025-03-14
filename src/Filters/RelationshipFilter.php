<?php

namespace RashadKhan\LaravelFilter\Filters;

use Illuminate\Database\Eloquent\Builder;
use RashadKhan\LaravelFilter\Contracts\FilterDriverInterface;

class RelationshipFilter extends AbstractFilter
{
    /**
     * Apply the filter to the query.
     *
     * @param Builder $query
     * @param string $relation
     * @param array $conditions
     * @param FilterDriverInterface $driver
     * @return Builder
     */
    public function apply($query, string $relation, array $conditions, FilterDriverInterface $driver)
    {
        return $query->whereHas($relation, function ($q) use ($conditions, $driver) {
            foreach ($conditions as $condition) {
                $driver->applyWhere(
                    $q,
                    $condition['field'],
                    $condition['operator'],
                    $condition['value']
                );
            }
        });
    }

    /**
     * Define relationship filter conditions.
     *
     * @param string $relation
     * @param array $conditions
     * @return array
     */
    public static function conditions(string $relation, array $conditions): array
    {
        return [
            'field' => $relation,
            'operator' => 'relation',
            'value' => $conditions
        ];
    }

    /**
     * Define a count filter for a relationship.
     *
     * @param string $relation
     * @param string $operator
     * @param int $count
     * @return array
     */
    public static function count(string $relation, string $operator, int $count): array
    {
        return [
            'field' => $relation,
            'operator' => 'relationCount',
            'value' => [$operator, $count]
        ];
    }
}
