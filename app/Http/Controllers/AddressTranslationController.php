<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\StoreAddressTranslationRequest;
use App\Http\Requests\Api\UpdateAddressTranslationRequest;
use App\Http\Resources\AddressTranslationResource;
use App\Models\AddressTranslation;

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
     *
     * @return AddressTranslationResource
     */
    public function store(StoreAddressTranslationRequest $request)
    {
        $data = $request->validated();

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
     *
     * @return AddressTranslationResource
     */
    public function update(UpdateAddressTranslationRequest $request, AddressTranslation $addressTranslation)
    {
        $data = $request->validated();

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
