<?php

namespace App\Http\Controllers;

use App\Http\Resources\LocationResource;
use App\Models\Location;
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
    public function index()
    {
        $locations = Location::with(['languages'])->get();

        return LocationResource::collection($locations);
    }

    /**
     * Store a newly created location.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'internal_name' => 'required|string|unique:locations,internal_name',
            'country_id' => 'required|exists:countries,id',
            'languages' => 'required|array|min:1',
            'languages.*.language_id' => 'required|exists:languages,id',
            'languages.*.name' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $location = Location::create([
            'internal_name' => $request->internal_name,
            'country_id' => $request->country_id,
            'backward_compatibility' => $request->backward_compatibility,
        ]);

        // Attach languages with names
        foreach ($request->languages as $languageData) {
            $location->languages()->attach($languageData['language_id'], [
                'name' => $languageData['name'],
            ]);
        }

        return (new LocationResource($location->load('languages')))->response()->setStatusCode(201);
    }

    /**
     * Display the specified location.
     *
     * @return \App\Http\Resources\LocationResource
     */
    public function show(Location $location)
    {
        return new LocationResource($location->load('languages'));
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
            'languages' => 'array|min:1',
            'languages.*.language_id' => 'required_with:languages|exists:languages,id',
            'languages.*.name' => 'required_with:languages|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $location->update([
            'internal_name' => $request->internal_name,
            'country_id' => $request->country_id,
            'backward_compatibility' => $request->backward_compatibility,
        ]);

        // Update languages if provided
        if ($request->has('languages')) {
            // First detach all existing languages
            $location->languages()->detach();

            // Attach new languages with names
            foreach ($request->languages as $languageData) {
                $location->languages()->attach($languageData['language_id'], [
                    'name' => $languageData['name'],
                ]);
            }
        }

        return new LocationResource($location->load('languages'));
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
