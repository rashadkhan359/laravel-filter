<?php

namespace RashadKhan\LaravelFilter\Contracts;

interface FilterDriverInterface
{
    /**
     * Apply a where condition to the query.
     *
     * @param mixed $query
     * @param string $fieldType
     * @param string $field
     * @param string $operator
     * @param mixed $value
     * @return mixed
     */
    public function applyWhere($query, string $fieldType, string $field, string $operator, $value);

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

    /**
     * Get the name of the driver.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Set the name of the driver.
     *
     * @param string $name
     * @return self
     */
    public function setName(string $name): self;

    /**
     * Map database column types to appropriate operators.
     *
     * @param string $columnType
     * @return array
     */
    public function mapColumnTypeToOperators(string $columnType): array;

    /**
     * Get operator mappings for this driver.
     *
     * @return array
     */
    public function getOperatorMappings(): array;

     /**
     * Get the adapter associated with the driver.
     *
     * @return mixed
     */
    public function getAdapter();

    /**
     * Set the adapter for the driver.
     *
     * @param mixed $adapter
     * @return self
     */
    public function setAdapter($adapter): self;
}
