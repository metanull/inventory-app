<?php

namespace App\Http\Controllers;

use App\Http\Requests\Location\DestroyLocationRequest;
use App\Http\Requests\Location\IndexLocationRequest;
use App\Http\Requests\Location\ShowLocationRequest;
use App\Http\Requests\Location\StoreLocationRequest;
use App\Http\Requests\Location\UpdateLocationRequest;
use App\Http\Resources\LocationResource;
use App\Models\Location;
use App\Support\Includes\AllowList;
use App\Support\Includes\IncludeParser;
use App\Support\Pagination\PaginationParams;
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
        $validatedData = $request->validated();
        $includes = IncludeParser::fromRequest($request, AllowList::for('location'));
        $pagination = PaginationParams::fromRequest($request);

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
        $validatedData = $request->validated();

        $location = Location::create([
            'internal_name' => $validatedData['internal_name'],
            'country_id' => $validatedData['country_id'],
            'backward_compatibility' => $validatedData['backward_compatibility'] ?? null,
        ]);

        // Create translations
        foreach ($validatedData['translations'] as $translationData) {
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
        $validatedData = $request->validated();
        $includes = IncludeParser::fromRequest($request, AllowList::for('location'));
        if (! empty($includes)) {
            $location->load($includes);
        }

        return new LocationResource($location);
    }

    /**
     * Update the specified location.
     *
     * @return \App\Http\Resources\LocationResource|\Illuminate\Http\JsonResponse
     */
    public function update(UpdateLocationRequest $request, Location $location)
    {
        $validatedData = $request->validated();

        $location->update([
            'internal_name' => $validatedData['internal_name'] ?? $location->internal_name,
            'country_id' => $validatedData['country_id'] ?? $location->country_id,
            'backward_compatibility' => $validatedData['backward_compatibility'] ?? $location->backward_compatibility,
        ]);

        // Update translations if provided
        if (isset($validatedData['translations'])) {
            // Delete existing translations
            $location->translations()->delete();

            // Create new translations
            foreach ($validatedData['translations'] as $translationData) {
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
    public function destroy(DestroyLocationRequest $request, Location $location)
    {
        $validatedData = $request->validated();
        $location->delete();

        return response()->noContent();
    }
}
