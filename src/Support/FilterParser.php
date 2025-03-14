<?php

namespace RashadKhan\LaravelFilter\Support;

use Illuminate\Http\Request;

class FilterParser
{
    /**
     * Parse filter parameters from request.
     *
     * @param Request $request
     * @return array
     */
    public static function fromRequest(Request $request): array
    {
        $filterParams = [];

        // Process search parameter
        if ($request->has('search')) {
            $filterParams['search'] = $request->input('search');
        }

        // Process standard filters
        if ($request->has('filter')) {
            $filterParams['filters'] = self::parseFilterParameter($request->input('filter'));
        }

        // Process sorting
        if ($request->has('sort')) {
            $filterParams['sort'] = self::parseSortParameter($request->input('sort'));
        }

        // Process pagination
        $filterParams['page'] = $request->input('page', 1);
        $filterParams['per_page'] = $request->input('per_page', 15);

        return $filterParams;
    }

    /**
     * Parse filter parameter from various formats.
     *
     * @param mixed $filter
     * @return array
     */
    protected static function parseFilterParameter($filter): array
    {
        // Handle JSON string
        if (is_string($filter) && self::isJson($filter)) {
            return json_decode($filter, true);
        }

        // Handle array
        if (is_array($filter)) {
            return $filter;
        }

        // Handle URL parameter style (field:operator:value,field2:operator2:value2)
        if (is_string($filter)) {
            $filters = [];
            $filterParts = explode(',', $filter);

            foreach ($filterParts as $part) {
                $segments = explode(':', $part);

                if (count($segments) >= 3) {
                    $field = $segments[0];
                    $operator = $segments[1];
                    $value = $segments[2];

                    // Handle special value types
                    if ($value === 'null') {
                        $value = null;
                    } elseif ($value === 'true') {
                        $value = true;
                    } elseif ($value === 'false') {
                        $value = false;
                    } elseif (strpos($value, '|') !== false) {
                        // Handle array values
                        $value = explode('|', $value);
                    }

                    $filters[] = [
                        'field' => $field,
                        'operator' => $operator,
                        'value' => $value,
                    ];
                }
            }

            return $filters;
        }

        return [];
    }

    /**
     * Parse sort parameter from various formats.
     *
     * @param mixed $sort
     * @return array|string
     */
    protected static function parseSortParameter($sort)
    {
        // Handle JSON string
        if (is_string($sort) && self::isJson($sort)) {
            return json_decode($sort, true);
        }

        // Handle array
        if (is_array($sort)) {
            return $sort;
        }

        // Handle string format (field:direction)
        if (is_string($sort)) {
            if (strpos($sort, ':') !== false) {
                return $sort;
            }

            // Default to ascending order if only field is provided
            return $sort . ':asc';
        }

        return [];
    }

    /**
     * Check if a string is valid JSON.
     *
     * @param string $string
     * @return bool
     */
    protected static function isJson($string): bool
    {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }
}
