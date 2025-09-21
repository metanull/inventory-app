<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProvinceTranslation\IndexProvinceTranslationRequest;
use App\Http\Requests\ProvinceTranslation\ShowProvinceTranslationRequest;
use App\Http\Requests\ProvinceTranslation\StoreProvinceTranslationRequest;
use App\Http\Requests\ProvinceTranslation\UpdateProvinceTranslationRequest;
use App\Http\Resources\ProvinceTranslationResource;
use App\Models\ProvinceTranslation;

class ProvinceTranslationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexProvinceTranslationRequest $request)
    {
        return ProvinceTranslationResource::collection(ProvinceTranslation::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProvinceTranslationRequest $request)
    {
        $translation = ProvinceTranslation::create($request->validated());

        return new ProvinceTranslationResource($translation);
    }

    /**
     * Display the specified resource.
     */
    public function show(ShowProvinceTranslationRequest $request, ProvinceTranslation $provinceTranslation)
    {
        return new ProvinceTranslationResource($provinceTranslation);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProvinceTranslationRequest $request, ProvinceTranslation $provinceTranslation)
    {
        $provinceTranslation->update($request->validated());

        return new ProvinceTranslationResource($provinceTranslation);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProvinceTranslation $provinceTranslation)
    {
        $provinceTranslation->delete();

        return response()->noContent();
    }
}
