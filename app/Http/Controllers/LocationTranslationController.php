<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\StoreLocationTranslationRequest;
use App\Http\Requests\Api\UpdateLocationTranslationRequest;
use App\Http\Resources\LocationTranslationResource;
use App\Models\LocationTranslation;

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
     *
     * @return LocationTranslationResource
     */
    public function store(StoreLocationTranslationRequest $request)
    {
        $data = $request->validated();

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
     *
     * @return LocationTranslationResource
     */
    public function update(UpdateLocationTranslationRequest $request, LocationTranslation $locationTranslation)
    {
        $data = $request->validated();

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
