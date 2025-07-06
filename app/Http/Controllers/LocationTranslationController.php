<?php

namespace App\Http\Controllers;

use App\Http\Resources\LocationTranslationResource;
use App\Models\LocationTranslation;
use Illuminate\Http\Request;

class LocationTranslationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return LocationTranslationResource::collection(LocationTranslation::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'location_id' => 'required|uuid|exists:locations,id',
            'language_id' => 'required|string|exists:languages,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $translation = LocationTranslation::create($data);

        return new LocationTranslationResource($translation);
    }

    /**
     * Display the specified resource.
     */
    public function show(LocationTranslation $locationTranslation)
    {
        return new LocationTranslationResource($locationTranslation);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LocationTranslation $locationTranslation)
    {
        $data = $request->validate([
            'location_id' => 'sometimes|uuid|exists:locations,id',
            'language_id' => 'sometimes|string|exists:languages,id',
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|nullable|string',
        ]);

        $locationTranslation->update($data);

        return new LocationTranslationResource($locationTranslation);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LocationTranslation $locationTranslation)
    {
        $locationTranslation->delete();

        return response()->noContent();
    }
}
