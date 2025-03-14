<?php

namespace RashadKhan\LaravelFilter\Exceptions;

use Exception;

class InvalidFilterException extends Exception
{
    /**
     * Create a new InvalidFilterException instance.
     *
     * @param string $message
     */
    public function __construct(string $message)
    {
        parent::__construct($message);
    }

    /**
     * Create an exception for an invalid filter.
     *
     * @param string $field
     * @param string $operator
     * @return static
     */
    public static function invalidFilter(string $field, string $operator): self
    {
        return new static("Invalid filter: {$field} with operator {$operator}");
    }
}
