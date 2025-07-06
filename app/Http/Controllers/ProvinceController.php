<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProvinceResource;
use App\Models\Province;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProvinceController extends Controller
{
    /**
     * Display a listing of provinces.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index()
    {
        $provinces = Province::with(['translations'])->get();

        return ProvinceResource::collection($provinces);
    }

    /**
     * Store a newly created province.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'internal_name' => 'required|string|unique:provinces,internal_name',
            'country_id' => 'required|exists:countries,id',
            'translations' => 'required|array|min:1',
            'translations.*.language_id' => 'required|exists:languages,id',
            'translations.*.name' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $province = Province::create([
            'internal_name' => $request->internal_name,
            'country_id' => $request->country_id,
            'backward_compatibility' => $request->backward_compatibility,
        ]);

        // Create translations
        foreach ($request->translations as $translationData) {
            $province->translations()->create([
                'language_id' => $translationData['language_id'],
                'name' => $translationData['name'],
            ]);
        }

        return (new ProvinceResource($province->load('translations')))->response()->setStatusCode(201);
    }

    /**
     * Display the specified province.
     *
     * @return \App\Http\Resources\ProvinceResource
     */
    public function show(Province $province)
    {
        return new ProvinceResource($province->load('translations'));
    }

    /**
     * Update the specified province.
     *
     * @return \App\Http\Resources\ProvinceResource|\Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Province $province)
    {
        $validator = Validator::make($request->all(), [
            'internal_name' => 'required|string|unique:provinces,internal_name,'.$province->id,
            'country_id' => 'required|exists:countries,id',
            'translations' => 'array|min:1',
            'translations.*.language_id' => 'required_with:translations|exists:languages,id',
            'translations.*.name' => 'required_with:translations|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $province->update([
            'internal_name' => $request->internal_name,
            'country_id' => $request->country_id,
            'backward_compatibility' => $request->backward_compatibility,
        ]);

        // Update translations if provided
        if ($request->has('translations')) {
            // Delete existing translations
            $province->translations()->delete();

            // Create new translations
            foreach ($request->translations as $translationData) {
                $province->translations()->create([
                    'language_id' => $translationData['language_id'],
                    'name' => $translationData['name'],
                ]);
            }
        }

        return new ProvinceResource($province->load('translations'));
    }

    /**
     * Remove the specified province.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Province $province)
    {
        $province->delete();

        return response()->noContent();
    }
}
