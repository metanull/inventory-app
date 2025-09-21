<?php

namespace App\Http\Controllers;

use App\Http\Requests\LocationTranslation\IndexLocationTranslationRequest;
use App\Http\Requests\LocationTranslation\ShowLocationTranslationRequest;
use App\Http\Requests\LocationTranslation\StoreLocationTranslationRequest;
use App\Http\Requests\LocationTranslation\UpdateLocationTranslationRequest;
use App\Http\Resources\LocationTranslationResource;
use App\Models\LocationTranslation;

class LocationTranslationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexLocationTranslationRequest $request)
    {
        return LocationTranslationResource::collection(LocationTranslation::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreLocationTranslationRequest $request)
    {
        $translation = LocationTranslation::create($request->validated());

        return new LocationTranslationResource($translation);
    }

    /**
     * Display the specified resource.
     */
    public function show(ShowLocationTranslationRequest $request, LocationTranslation $locationTranslation)
    {
        return new LocationTranslationResource($locationTranslation);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateLocationTranslationRequest $request, LocationTranslation $locationTranslation)
    {
        $locationTranslation->update($request->validated());

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
