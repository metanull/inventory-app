<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\IndexLocationRequest;
use App\Http\Requests\Api\ShowLocationRequest;
use App\Http\Requests\Api\StoreLocationRequest;
use App\Http\Requests\Api\UpdateLocationRequest;
use App\Http\Resources\LocationResource;
use App\Models\Location;
use App\Support\Includes\AllowList;
use App\Support\Includes\IncludeParser;
use Illuminate\Http\JsonResponse;

class LocationController extends Controller
{
    /**
     * Display a listing of locations.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(IndexLocationRequest $request)
    {
        $includes = $request->getIncludeParams();
        $pagination = $request->getPaginationParams();

        $defaults = ['translations'];
        $with = array_values(array_unique(array_merge($defaults, $includes)));
        $query = Location::query()->with($with);

        $paginator = $query->paginate(
            $pagination['per_page'],
            ['*'],
            'page',
            $pagination['page']
        );

        return LocationResource::collection($paginator);
    }

    /**
     * Store a newly created location.
     */
    public function store(StoreLocationRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $location = Location::create([
            'internal_name' => $validated['internal_name'],
            'country_id' => $validated['country_id'],
            'backward_compatibility' => $validated['backward_compatibility'] ?? null,
        ]);

        // Create translations
        foreach ($validated['translations'] as $translationData) {
            $location->translations()->create([
                'language_id' => $translationData['language_id'],
                'name' => $translationData['name'],
            ]);
        }

        $requested = IncludeParser::fromRequest($request, AllowList::for('location'));
        $defaults = ['translations'];
        $location->load(array_values(array_unique(array_merge($defaults, $requested))));

        return (new LocationResource($location))->response()->setStatusCode(201);
    }

    /**
     * Display the specified location.
     *
     * @return \App\Http\Resources\LocationResource
     */
    public function show(ShowLocationRequest $request, Location $location)
    {
        $includes = $request->getIncludeParams();
        if (! empty($includes)) {
            $location->load($includes);
        }

        return new LocationResource($location);
    }

    /**
     * Update the specified location.
     *
     * @return \App\Http\Resources\LocationResource
     */
    public function update(UpdateLocationRequest $request, Location $location)
    {
        $validated = $request->validated();

        $location->update([
            'internal_name' => $validated['internal_name'],
            'country_id' => $validated['country_id'],
            'backward_compatibility' => $validated['backward_compatibility'] ?? null,
        ]);

        // Update translations if provided
        if (isset($validated['translations'])) {
            // Delete existing translations
            $location->translations()->delete();

            // Create new translations
            foreach ($validated['translations'] as $translationData) {
                $location->translations()->create([
                    'language_id' => $translationData['language_id'],
                    'name' => $translationData['name'],
                ]);
            }
        }

        $requested = IncludeParser::fromRequest($request, AllowList::for('location'));
        $defaults = ['translations'];
        $location->load(array_values(array_unique(array_merge($defaults, $requested))));

        return new LocationResource($location);
    }

    /**
     * Remove the specified location.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Location $location)
    {
        $location->delete();

        return response()->noContent();
    }
}
