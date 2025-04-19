<?php

namespace RashadKhan\LaravelFilter\Support;

use Illuminate\Http\Request;

class FilterParser
{
    /**
     * Parse filter parameters from a request.
     *
     * @param Request $request
     * @return array
     */
    public static function fromRequest(Request $request): array
    {
        $params = [];

        // Parse search parameter
        if ($request->has('search')) {
            $params['search'] = $request->input('search');
        }

        // Parse filters
        if ($request->has('filters')) {
            $params['filters'] = self::parseFilters($request->input('filters'));
        }

        // Parse sort parameters
        if ($request->has('sort')) {
            $params['sort'] = self::parseSort($request->input('sort'));
        }

        // Parse pagination
        $params['per_page'] = $request->input('per_page', config('laravelfilter.default_per_page', 15));
        $params['page'] = $request->input('page', 1);

        return $params;
    }

    /**
     * Parse filters from various input formats.
     *
     * @param mixed $filters
     * @return array
     */
    public static function parseFilters($filters): array
    {
        // Handle JSON string
        if (is_string($filters)) {
            $filters = json_decode($filters, true);
        }

        // Default to empty array if not valid
        if (!is_array($filters)) {
            return [
                'logic' => 'and',
                'conditions' => []
            ];
        }

        // Check if this is a new format with logic and groups/conditions
        if (isset($filters['logic'])) {
            return $filters;
        }

        // Convert from various legacy formats to the new format

        // Format: [{'field': 'name', 'operator': 'eq', 'value': 'John'}]
        if (isset($filters[0]) && is_array($filters[0]) && isset($filters[0]['field'])) {
            return [
                'logic' => 'and',
                'conditions' => $filters
            ];
        }

        // Format: {'name': 'John', 'age': {'operator': 'gt', 'value': 18}}
        $conditions = [];
        foreach ($filters as $field => $value) {
            if (is_array($value) && isset($value['operator'])) {
                $conditions[] = [
                    'field' => $field,
                    'operator' => $value['operator'],
                    'value' => $value['value']
                ];
            } else {
                $conditions[] = [
                    'field' => $field,
                    'operator' => 'eq',
                    'value' => $value
                ];
            }
        }

        return [
            'logic' => 'and',
            'conditions' => $conditions
        ];
    }

    /**
     * Parse sort parameters from various formats.
     *
     * @param mixed $sort
     * @return array|string
     */
    public static function parseSort($sort)
    {
        // Handle JSON string
        if (is_string($sort) && self::isJson($sort)) {
            $sort = json_decode($sort, true);
        }

        // Handle format like "-created_at" (for descending)
        if (is_string($sort) && !str_contains($sort, ':')) {
            $direction = 'asc';

            if (str_starts_with($sort, '-')) {
                $direction = 'desc';
                $sort = substr($sort, 1);
            }

            return $sort . ':' . $direction;
        }

        return $sort;
    }

    /**
     * Check if a string is valid JSON.
     *
     * @param string $string
     * @return bool
     */
    private static function isJson(string $string): bool
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }
}
