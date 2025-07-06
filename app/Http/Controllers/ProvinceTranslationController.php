<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProvinceTranslationResource;
use App\Models\ProvinceTranslation;
use Illuminate\Http\Request;

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
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'province_id' => 'required|uuid|exists:provinces,id',
            'language_id' => 'required|string|exists:languages,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

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
     */
    public function update(Request $request, ProvinceTranslation $provinceTranslation)
    {
        $data = $request->validate([
            'province_id' => 'sometimes|uuid|exists:provinces,id',
            'language_id' => 'sometimes|string|exists:languages,id',
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|nullable|string',
        ]);

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
