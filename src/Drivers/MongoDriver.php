<?php

namespace RashadKhan\LaravelFilter\Drivers;

use Jenssegers\Mongodb\Eloquent\Builder;
use RashadKhan\LaravelFilter\Adapters\MongoSchemaAdapter;
use RashadKhan\LaravelFilter\Contracts\FilterDriverInterface;

class MongoDriver extends AbstractDriver implements FilterDriverInterface
{
    public function __construct()
    {
        $this->adapter = new MongoSchemaAdapter;
    }
    /**
     * Apply a where condition to the query.
     *
     * @param Builder $query
     * @param string $fieldType
     * @param string $field
     * @param string $operator
     * @param mixed $value
     * @return Builder
     */
    public function applyWhere($query, $fieldType, string $field, string $operator, $value)
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
                // MongoDB uses regex for like operations
                return $query->where($field, 'regex', new \MongoDB\BSON\Regex($value, 'i'));
            case 'in':
                return $query->whereIn($field, $value);
            case 'between':
                return $query->whereBetween($field, $value);
            case 'null':
                return $query->whereNull($field);
            case 'not_null':
                return $query->whereNotNull($field);
            case 'exists':
                return $query->whereRaw([$field => ['$exists' => true]]);
            case 'type':
                return $query->whereRaw([$field => ['$type' => $value]]);
            case 'relation':
                // MongoDB has different relationship handling
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
                // MongoDB uses regex for text searching
                $q->orWhere($field, 'regex', new \MongoDB\BSON\Regex($searchTerm, 'i'));
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

    /**
     * Map database column types to appropriate operators for MongoDB.
     *
     * @param string $columnType
     * @return array
     */
    public function mapColumnTypeToOperators(string $columnType): array
    {
        // MongoDB-specific mappings
        $mongoTypeMap = [
            'int' => ['eq', 'neq', 'gt', 'gte', 'lt', 'lte', 'in', 'between'],
            'long' => ['eq', 'neq', 'gt', 'gte', 'lt', 'lte', 'in', 'between'],
            'double' => ['eq', 'neq', 'gt', 'gte', 'lt', 'lte', 'in', 'between'],
            'string' => ['eq', 'neq', 'regex'],
            'objectId' => ['eq', 'neq', 'in'],
            'bool' => ['eq'],
            'date' => ['eq', 'neq', 'gt', 'gte', 'lt', 'lte', 'between'],
            'array' => ['eq', 'in', 'size', 'exists'],
            'object' => ['exists'],
        ];

        // Merge with parent mappings and return
        $baseTypeMap = parent::mapColumnTypeToOperators($columnType);
        $mergedTypeMap = array_merge($baseTypeMap, $mongoTypeMap);

        return $mergedTypeMap[$columnType] ?? $mergedTypeMap['default'] ?? ['eq', 'neq'];
    }
}
