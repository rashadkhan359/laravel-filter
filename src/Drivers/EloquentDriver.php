<?php

namespace RashadKhan\LaravelFilter\Drivers;

use Illuminate\Database\Eloquent\Builder;
use RashadKhan\LaravelFilter\Contracts\FilterDriverInterface;

class EloquentDriver extends AbstractDriver implements FilterDriverInterface
{
    /**
     * Apply a where condition to the query.
     *
     * @param Builder $query
     * @param string $field
     * @param string $operator
     * @param mixed $value
     * @return Builder
     */
    public function applyWhere($query, string $field, string $operator, $value)
    {
        switch ($operator) {
            case 'eq':
                return $query->where($field, '=', $value);
            case 'neq':
                return $query->where($field, '!=', $value);
            case 'gt':
                return $query->where($field, '>', $value);
            case 'gte':
                return $query->where($field, '>=', $value);
            case 'lt':
                return $query->where($field, '<', $value);
            case 'lte':
                return $query->where($field, '<=', $value);
            case 'like':
                return $query->where($field, 'LIKE', "%{$value}%");
            case 'in':
                return $query->whereIn($field, $value);
            case 'between':
                return $query->whereBetween($field, $value);
            case 'null':
                return $query->whereNull($field);
            case 'not_null':
                return $query->whereNotNull($field);
            case 'date':
                return $query->whereDate($field, '=', $value);
            case 'year':
                return $query->whereYear($field, '=', $value);
            case 'month':
                return $query->whereMonth($field, '=', $value);
            case 'day':
                return $query->whereDay($field, '=', $value);
            case 'time':
                return $query->whereTime($field, '=', $value);
            case 'relation':
                list($relation, $relationField, $relationOperator, $relationValue) = $value;
                return $query->whereHas($relation, function ($q) use ($relationField, $relationOperator, $relationValue) {
                    $this->applyWhere($q, $relationField, $relationOperator, $relationValue);
                });
            default:
                return $query;
        }
    }

    /**
     * Apply an order by clause to the query.
     *
     * @param Builder $query
     * @param string $field
     * @param string $direction
     * @return Builder
     */
    public function applyOrderBy($query, string $field, string $direction)
    {
        return $query->orderBy($field, $direction);
    }

    /**
     * Apply a search condition to the query.
     *
     * @param Builder $query
     * @param array $fields
     * @param string $searchTerm
     * @return Builder
     */
    public function applySearch($query, array $fields, string $searchTerm)
    {
        if (empty(trim($searchTerm))) {
            return $query;
        }

        return $query->where(function ($q) use ($fields, $searchTerm) {
            foreach ($fields as $field) {
                if (str_contains($field, '.')) {
                    // Handle relationship fields
                    list($relation, $column) = explode('.', $field, 2);
                    $q->orWhereHas($relation, function ($subQ) use ($column, $searchTerm) {
                        $subQ->where($column, 'LIKE', "%{$searchTerm}%");
                    });
                } else {
                    $q->orWhere($field, 'LIKE', "%{$searchTerm}%");
                }
            }
        });
    }

    /**
     * Apply pagination to the query.
     *
     * @param Builder $query
     * @param int $perPage
     * @param int $page
     * @return mixed
     */
    public function applyPagination($query, int $perPage, int $page)
    {
        return $query->paginate($perPage, ['*'], 'page', $page);
    }
}
