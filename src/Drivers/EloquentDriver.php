<?php

namespace RashadKhan\LaravelFilter\Drivers;

use Illuminate\Database\Eloquent\Builder;
use RashadKhan\LaravelFilter\Support\FilterResolver;
use RashadKhan\LaravelFilter\Contracts\FilterDriverInterface;

class EloquentDriver extends AbstractDriver implements FilterDriverInterface
{

    protected $filterResolver;

    public function __construct()
    {
        $this->filterResolver = new FilterResolver;
    }

    /**
     * Apply a where condition to the query.
     *
     * @param Builder $query
     * @param string $fieldType
     * @param string $field
     * @param string $operator
     * @param mixed $value
     * @param string $logic
     * @return Builder
     */
    public function applyWhere($query, string $fieldType, string $field, string $operator, $value, string $logic = 'and')
    {
        return $this->getFilterClass($fieldType)
            ->apply($query, $field, $operator, $value, $logic);
    }

    /**
     * Apply an OR where condition to the query.
     *
     * @param Builder $query
     * @param string $fieldType
     * @param string $field
     * @param string $operator
     * @param mixed $value
     * @return Builder
     */
    public function applyOrWhere($query, string $fieldType, string $field, string $operator, $value)
    {
        return $this->getFilterClass($fieldType)
            ->apply($query, $field, $operator, $value, 'or');
    }

    public function getFilterClass(string $fieldType)
    {
        return $this->filterResolver->resolveFilterForField($fieldType);
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
                        // Fix SQL injection vulnerability by using parameters
                        $subQ->where($column, 'LIKE', '%' . $searchTerm . '%');
                    });
                } else {
                    // Fix SQL injection vulnerability by using parameters
                    $q->orWhere($field, 'LIKE', '%' . $searchTerm . '%');
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

    /**
     * Map database column types to appropriate operators for SQL databases.
     *
     * @param string $columnType
     * @return array
     */
    public function mapColumnTypeToOperators(string $columnType): array
    {
        // Get dynamically allowed operators
        // $availableOperators = $this->filterResolver->getFilterCases($columnType);

        // // Get base mappings from the parent class
        // $baseTypeMap = parent::mapColumnTypeToOperators($columnType);
        // // Filter out any operators that are not supported in the system
        // return array_values(array_intersect($baseTypeMap, $availableOperators));
        return $this->filterResolver->getFilterCases($columnType);
    }


}
