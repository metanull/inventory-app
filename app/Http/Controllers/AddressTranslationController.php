<?php

namespace App\Http\Controllers;

use App\Http\Resources\AddressTranslationResource;
use App\Models\AddressTranslation;
use Illuminate\Http\Request;

class AddressTranslationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return AddressTranslationResource::collection(AddressTranslation::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'address_id' => 'required|uuid|exists:addresses,id',
            'language_id' => 'required|string|exists:languages,id',
            'address' => 'required|string',
            'description' => 'nullable|string',
        ]);

        $translation = AddressTranslation::create($data);

        return new AddressTranslationResource($translation);
    }

    /**
     * Display the specified resource.
     */
    public function show(AddressTranslation $addressTranslation)
    {
        return new AddressTranslationResource($addressTranslation);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AddressTranslation $addressTranslation)
    {
        $data = $request->validate([
            'address_id' => 'sometimes|uuid|exists:addresses,id',
            'language_id' => 'sometimes|string|exists:languages,id',
            'address' => 'sometimes|string',
            'description' => 'sometimes|nullable|string',
        ]);

        $addressTranslation->update($data);

        return new AddressTranslationResource($addressTranslation);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AddressTranslation $addressTranslation)
    {
        $addressTranslation->delete();

        return response()->noContent();
    }
}
