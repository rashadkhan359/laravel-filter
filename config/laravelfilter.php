<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Filter Driver
    |--------------------------------------------------------------------------
    |
    | This option controls the default filter driver that will be used to apply
    | filters. You may set this to any of the drivers defined in the
    | "available_drivers" array below.
    |
    */
    'default_driver' => env('QUERY_FILTER_DRIVER', 'eloquent'),

    /*
    |--------------------------------------------------------------------------
    | Available Filter Drivers
    |--------------------------------------------------------------------------
    |
    | This array defines all the filter drivers that can be used. The key is
    | the driver name, and the value is the full class name of the driver.
    |
    */
    'available_drivers' => [
        'eloquent' => RashadKhan\LaravelFilter\Drivers\EloquentDriver::class,
        'mongo' => RashadKhan\LaravelFilter\Drivers\MongoDriver::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Filter Namespace
    |--------------------------------------------------------------------------
    |
    | This option controls the default namespace for filter classes. You may
    | change this to any namespace you want to organize your filters.
    |
    */
    'filter_namespace' => 'App\\Filters',

    /*
    |--------------------------------------------------------------------------
    | Filter Schema Cache
    |--------------------------------------------------------------------------
    |
    | This option enables caching for filter schemas. When enabled, the package
    | will cache the allowed filters and operations for each model.
    |
    */
    'enable_cache' => env('QUERY_FILTER_CACHE', true),

    /*
    |--------------------------------------------------------------------------
    | Cache Duration
    |--------------------------------------------------------------------------
    |
    | This option controls how long (in minutes) the filter schemas will be
    | cached. Set to null for indefinite caching.
    |
    */
    'cache_duration' => 60,

    /*
    |--------------------------------------------------------------------------
    | Default Operators
    |--------------------------------------------------------------------------
    |
    | This option defines the default operators that are available for all
    | filters. You can add or remove operators as needed.
    |
    */
    'default_operators' => [
        'eq' => '=',
        'neq' => '!=',
        'gt' => '>',
        'gte' => '>=',
        'lt' => '<',
        'lte' => '<=',
        'like' => 'LIKE',
        'in' => 'IN',
        'between' => 'BETWEEN',
        'null' => 'IS NULL',
        'not_null' => 'IS NOT NULL',
    ],

    /*
    |--------------------------------------------------------------------------
    | API Response Format
    |--------------------------------------------------------------------------
    |
    | This option controls the format of API responses. You can choose between
    | 'standard', 'jsonapi', or 'custom'.
    |
    */
    'response_format' => 'standard',

    /*
    |--------------------------------------------------------------------------
    | Default Pagination Size
    |--------------------------------------------------------------------------
    |
    | This option controls the default number of records per page when
    | pagination is enabled.
    |
    */
    'default_per_page' => 15,

    /*
    |--------------------------------------------------------------------------
    | Auto-discover Filters
    |--------------------------------------------------------------------------
    |
    | When enabled, the package will automatically discover and register
    | filters based on model names.
    |
    */
    'auto_discover' => true,
];
