<?php

namespace RashadKhan\LaravelFilter\Support;

use InvalidArgumentException;
use RashadKhan\LaravelFilter\Filters\DateFilter;
use RashadKhan\LaravelFilter\Filters\TextFilter;
use RashadKhan\LaravelFilter\Filters\BooleanFilter;
use RashadKhan\LaravelFilter\Filters\NumericFilter;
use RashadKhan\LaravelFilter\Filters\RelationshipFilter;
use ReflectionClass;

class FilterResolver
{
    /**
     * @var array Filter class mappings
     */
    protected $typeMap = [
        // String types
        'string' => TextFilter::class,
        'char' => TextFilter::class,
        'varchar' => TextFilter::class,
        'text' => TextFilter::class,
        'longtext' => TextFilter::class,
        'mediumtext' => TextFilter::class,
        'enum' => TextFilter::class,
        'set' => TextFilter::class,

        // Numeric types
        'int' => NumericFilter::class,
        'integer' => NumericFilter::class,
        'tinyint' => NumericFilter::class,
        'smallint' => NumericFilter::class,
        'mediumint' => NumericFilter::class,
        'bigint' => NumericFilter::class,
        'float' => NumericFilter::class,
        'double' => NumericFilter::class,
        'decimal' => NumericFilter::class,
        'numeric' => NumericFilter::class,

        // Date types
        'date' => DateFilter::class,
        'datetime' => DateFilter::class,
        'timestamp' => DateFilter::class,
        'time' => DateFilter::class,
        'year' => DateFilter::class,

        // Boolean types
        'boolean' => BooleanFilter::class,
        'bool' => BooleanFilter::class,

        'relation' => RelationshipFilter::class,

        // Default
        'default' => TextFilter::class
    ];

    protected $customMappings = [];

    public function registerCustomMapping(string $type, string $filterClass)
    {
        $this->customMappings[$type] = $filterClass;
        return $this;
    }

    public function resolveFilterForField(string $columnType)
    {
        $filterClass = $this->customMappings[$columnType]
            ?? $this->typeMap[$columnType]
            ?? $this->typeMap['default'];

        return new $filterClass();
    }

    /**
     * Get all cases from the apply method of a given filter class.
     *
     * @param string $filterClass
     * @return array
     */
    public function getFilterCases(string $filterType): array
    {

        // Resolve the filter class name
        $filterInstance = $this->resolveFilterForField($filterType);

        $filterClass = get_class($filterInstance); // Get actual class name

        if (!class_exists($filterClass)) {
            throw new InvalidArgumentException("Class {$filterClass} does not exist.");
        }

        $reflection = new ReflectionClass($filterClass);

        if (!$reflection->hasMethod('apply')) {
            throw new InvalidArgumentException("Class {$filterClass} does not have an apply method.");
        }

        $method = $reflection->getMethod('apply');
        $source = file($method->getFileName());
        $methodCode = implode("", array_slice($source, $method->getStartLine() - 1, $method->getEndLine() - $method->getStartLine() + 1));

        preg_match_all("/case '(.*?)':/", $methodCode, $matches);

        return $matches[1] ?? [];
    }
}
