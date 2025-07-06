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
        $provinces = Province::with(['languages'])->get();

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
            'languages' => 'required|array|min:1',
            'languages.*.language_id' => 'required|exists:languages,id',
            'languages.*.name' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $province = Province::create([
            'internal_name' => $request->internal_name,
            'country_id' => $request->country_id,
            'backward_compatibility' => $request->backward_compatibility,
        ]);

        // Attach languages with names
        foreach ($request->languages as $languageData) {
            $province->languages()->attach($languageData['language_id'], [
                'name' => $languageData['name'],
            ]);
        }

        return (new ProvinceResource($province->load('languages')))->response()->setStatusCode(201);
    }

    /**
     * Display the specified province.
     *
     * @return \App\Http\Resources\ProvinceResource
     */
    public function show(Province $province)
    {
        return new ProvinceResource($province->load('languages'));
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
            'languages' => 'array|min:1',
            'languages.*.language_id' => 'required_with:languages|exists:languages,id',
            'languages.*.name' => 'required_with:languages|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $province->update([
            'internal_name' => $request->internal_name,
            'country_id' => $request->country_id,
            'backward_compatibility' => $request->backward_compatibility,
        ]);

        // Update languages if provided
        if ($request->has('languages')) {
            // First detach all existing languages
            $province->languages()->detach();

            // Attach new languages with names
            foreach ($request->languages as $languageData) {
                $province->languages()->attach($languageData['language_id'], [
                    'name' => $languageData['name'],
                ]);
            }
        }

        return new ProvinceResource($province->load('languages'));
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
