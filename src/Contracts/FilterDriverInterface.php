<?php

namespace RashadKhan\LaravelFilter\Contracts;

interface FilterDriverInterface
{
    /**
     * Apply a where condition to the query.
     *
     * @param mixed $query
     * @param string $field
     * @param string $operator
     * @param mixed $value
     * @return mixed
     */
    public function applyWhere($query, string $field, string $operator, $value);

    /**
     * Apply an order by clause to the query.
     *
     * @param mixed $query
     * @param string $field
     * @param string $direction
     * @return mixed
     */
    public function applyOrderBy($query, string $field, string $direction);

    /**
     * Apply a search condition to the query.
     *
     * @param mixed $query
     * @param array $fields
     * @param string $searchTerm
     * @return mixed
     */
    public function applySearch($query, array $fields, string $searchTerm);

    /**
     * Apply pagination to the query.
     *
     * @param mixed $query
     * @param int $perPage
     * @param int $page
     * @return mixed
     */
    public function applyPagination($query, int $perPage, int $page);
}
