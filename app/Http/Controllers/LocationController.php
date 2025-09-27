<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\IndexLocationRequest;
use App\Http\Requests\Api\ShowLocationRequest;
use App\Http\Resources\LocationResource;
use App\Models\Location;
use App\Support\Includes\AllowList;
use App\Support\Includes\IncludeParser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'internal_name' => 'required|string|unique:locations,internal_name',
            'country_id' => 'required|exists:countries,id',
            'translations' => 'required|array|min:1',
            'translations.*.language_id' => 'required|exists:languages,id',
            'translations.*.name' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $location = Location::create([
            'internal_name' => $request->internal_name,
            'country_id' => $request->country_id,
            'backward_compatibility' => $request->backward_compatibility,
        ]);

        // Create translations
        foreach ($request->translations as $translationData) {
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
     * @return \App\Http\Resources\LocationResource|\Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Location $location)
    {
        $validator = Validator::make($request->all(), [
            'internal_name' => 'required|string|unique:locations,internal_name,'.$location->id,
            'country_id' => 'required|exists:countries,id',
            'translations' => 'array|min:1',
            'translations.*.language_id' => 'required_with:translations|exists:languages,id',
            'translations.*.name' => 'required_with:translations|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $location->update([
            'internal_name' => $request->internal_name,
            'country_id' => $request->country_id,
            'backward_compatibility' => $request->backward_compatibility,
        ]);

        // Update translations if provided
        if ($request->has('translations')) {
            // Delete existing translations
            $location->translations()->delete();

            // Create new translations
            foreach ($request->translations as $translationData) {
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
