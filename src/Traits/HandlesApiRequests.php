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
}
