<?php

namespace RashadKhan\LaravelFilter\Drivers;

use RashadKhan\LaravelFilter\Contracts\FilterDriverInterface;

abstract class AbstractDriver implements FilterDriverInterface
{
    /**
     * The name of the driver.
     *
     * @var string
     */
    protected $name;

    /**
     * The adapter associated with the driver.
     *
     * @var mixed
     */
    protected $adapter;

    /**
     * Get the name of the driver.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Set the name of the driver.
     *
     * @param string $name
     * @return self
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get the adapter associated with the driver.
     *
     * @return mixed
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * Set the adapter for the driver.
     *
     * @param mixed $adapter
     * @return self
     */
    public function setAdapter($adapter): self
    {
        $this->adapter = $adapter;
        return $this;
    }

    /**
     * Get operator mappings for this driver.
     *
     * @return array
     */
    public function getOperatorMappings(): array
    {
        return [
            'eq' => '=',
            'neq' => '!=',
            'gt' => '>',
            'gte' => '>=',
            'lt' => '<',
            'lte' => '<=',
            'like' => 'LIKE',
            'in' => 'IN',
            'not_in' => 'NOT IN',
            'between' => 'BETWEEN',
            'not_between' => 'NOT BETWEEN',
            'null' => 'IS NULL',
            'not_null' => 'IS NOT NULL',
        ];
    }

    /**
     * Map database column types to appropriate operators.
     *
     * @param string $columnType
     * @return array
     */
    public function mapColumnTypeToOperators(string $columnType): array
    {
        // Base mappings that work across most databases
        $typeMap = [
            // Numeric types
            'integer' => ['eq', 'neq', 'gt', 'gte', 'lt', 'lte', 'in', 'between'],
            'bigint' => ['eq', 'neq', 'gt', 'gte', 'lt', 'lte', 'in', 'between'],
            'float' => ['eq', 'neq', 'gt', 'gte', 'lt', 'lte', 'in', 'between'],
            'double' => ['eq', 'neq', 'gt', 'gte', 'lt', 'lte', 'in', 'between'],
            'decimal' => ['eq', 'neq', 'gt', 'gte', 'lt', 'lte', 'in', 'between'],

            // String types
            'string' => ['eq', 'neq', 'like'],
            'varchar' => ['eq', 'neq', 'like'],
            'text' => ['eq', 'neq', 'like'],

            // Date types
            'datetime' => ['eq', 'neq', 'gt', 'gte', 'lt', 'lte', 'between'],
            'date' => ['eq', 'neq', 'gt', 'gte', 'lt', 'lte', 'between'],
            'timestamp' => ['eq', 'neq', 'gt', 'gte', 'lt', 'lte', 'between'],

            // Boolean type
            'boolean' => ['eq'],
            'bool' => ['eq'],

            // Default
            'default' => ['eq', 'neq']
        ];

        return $typeMap[$columnType] ?? $typeMap['default'];
    }
}
