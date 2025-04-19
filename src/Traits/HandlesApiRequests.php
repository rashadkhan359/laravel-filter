<?php

namespace RashadKhan\LaravelFilter\Traits;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Resources\Json\JsonResource;
use RashadKhan\LaravelFilter\Support\FilterParser;

trait HandlesApiRequests
{
    /**
     * Process an API index request with filtering capabilities.
     *
     * @param Request $request
     * @param string|Model|Builder $query - Model class name, model instance, or query builder
     * @param string|null $resourceClass - Resource class for response transformation
     * @return \Illuminate\Http\JsonResponse
     */
    protected function filteredIndex(Request $request, $query, ?string $resourceClass = null)
    {
        // Handle different input types for $query
        if (is_string($query)) {
            // If a string class name is provided, instantiate the model
            $model = new $query();
            $query = $model->newQuery();
        } elseif ($query instanceof Model) {
            // If a model instance is provided, create a new query
            $model = $query;
            $query = $model->newQuery();
        } elseif ($query instanceof Builder) {
            // If a query builder is provided, use it directly
            $model = $query->getModel();
        } else {
            throw new \InvalidArgumentException('Query must be a model class name, model instance, or query builder');
        }

        // Parse filter parameters from the request
        $filterParams = FilterParser::fromRequest($request);

        // Get filtered results
        $results = $query->filter($filterParams);

        // Transform results with resource if provided
        if ($resourceClass) {
            $results = $resourceClass::collection($results);
        }

        return response()->json([
            'data' => $results->items(),
            'meta' => [
                'current_page' => $results->currentPage(),
                'from' => $results->firstItem(),
                'last_page' => $results->lastPage(),
                'path' => $request->url(),
                'per_page' => $results->perPage(),
                'to' => $results->lastItem(),
                'total' => $results->total(),
            ],
            // 'filter_schema' => [
            //     'allowed_filters' => $model->getAvailableFilters(),
            //     'searchable_fields' => $model->getSearchableFields(),
            // ],
        ]);
    }

    /**
     * Apply filters from the request to the query.
     *
     * @param Request $request
     * @param Builder|Model $query
     * @return mixed
     */
    public function applyFiltersFromRequest(Request $request, $query)
    {
        $filterParams = $this->extractFilterParams($request);
        return $this->filter($filterParams, $query);
    }

    /**
     * Extract filter parameters from the request.
     *
     * @param Request $request
     * @return array
     */
    protected function extractFilterParams(Request $request): array
    {
        $params = [];

        // Extract search
        if ($request->has('search')) {
            $params['search'] = $request->input('search');
        }

        // Extract filters
        if ($request->has('filters')) {
            $params['filters'] = $this->parseFiltersFromRequest($request->input('filters'));
        }

        // Extract sort
        if ($request->has('sort')) {
            $params['sort'] = $this->parseSortFromRequest($request->input('sort'));
        }

        // Extract pagination
        $params['per_page'] = $request->input('per_page', config('laravelfilter.default_per_page', 15));
        $params['page'] = $request->input('page', 1);

        return $params;
    }

    /**
     * Parse filters from request input.
     *
     * @param mixed $filters
     * @return array
     */
    protected function parseFiltersFromRequest($filters): array
    {
        // Handle JSON string
        if (is_string($filters)) {
            $filters = json_decode($filters, true);
        }

        // Default to empty array if not valid
        if (!is_array($filters)) {
            return [];
        }

        // Check if this is a new format with logic and groups
        if (isset($filters['logic'])) {
            return $filters;
        }

        // Convert to new format if using legacy format
        return [
            'logic' => 'and',
            'conditions' => $filters
        ];
    }

    /**
     * Parse sort parameters from request.
     *
     * @param mixed $sort
     * @return array|string
     */
    protected function parseSortFromRequest($sort)
    {
        // Handle JSON string
        if (is_string($sort) && $this->isJson($sort)) {
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
    protected function isJson(string $string): bool
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }
}
