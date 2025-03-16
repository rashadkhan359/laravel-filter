<?php

namespace RashadKhan\LaravelFilter\Traits;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\JsonResource;
use RashadKhan\LaravelFilter\Support\FilterParser;

trait HandlesApiRequests
{
    /**
     * Process an API index request with filtering capabilities.
     *
     * @param Request $request
     * @param string $modelClass
     * @param string|null $resourceClass
     * @return \Illuminate\Http\JsonResponse
     */
    protected function filteredIndex(Request $request, string $modelClass, ?string $resourceClass = null)
    {
        // Create a new instance of the model
        $model = new $modelClass();

        // Parse filter parameters from the request
        $filterParams = FilterParser::fromRequest($request);

        // Get filtered results
        $results = $model->filter($filterParams);

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
            'filter_schema' => [
                'allowed_filters' => $model->getAvailableFilters(),
                'searchable_fields' => $model->getSearchableFields(),
            ],
        ]);
    }
}
