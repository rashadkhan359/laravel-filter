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
}
