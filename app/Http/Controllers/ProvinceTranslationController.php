<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\StoreProvinceTranslationRequest;
use App\Http\Requests\Api\UpdateProvinceTranslationRequest;
use App\Http\Resources\ProvinceTranslationResource;
use App\Models\ProvinceTranslation;

class ProvinceTranslationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return ProvinceTranslationResource::collection(ProvinceTranslation::all());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return ProvinceTranslationResource
     */
    public function store(StoreProvinceTranslationRequest $request)
    {
        $data = $request->validated();

        $translation = ProvinceTranslation::create($data);

        return new ProvinceTranslationResource($translation);
    }

    /**
     * Display the specified resource.
     */
    public function show(ProvinceTranslation $provinceTranslation)
    {
        return new ProvinceTranslationResource($provinceTranslation);
    }

    /**
     * Update the specified resource in storage.
     *
     * @return ProvinceTranslationResource
     */
    public function update(UpdateProvinceTranslationRequest $request, ProvinceTranslation $provinceTranslation)
    {
        $data = $request->validated();

        $provinceTranslation->update($data);

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
