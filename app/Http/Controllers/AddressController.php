<?php

namespace App\Http\Controllers;

use App\Http\Resources\AddressResource;
use App\Models\Address;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AddressController extends Controller
{
    /**
     * Display a listing of addresses.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index()
    {
        $addresses = Address::with(['languages'])->get();

        return AddressResource::collection($addresses);
    }

    /**
     * Store a newly created address.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'id' => 'prohibited',
            'internal_name' => 'required|string|unique:addresses,internal_name',
            'country_id' => 'required|exists:countries,id',
            'languages' => 'required|array|min:1',
            'languages.*.language_id' => 'required|exists:languages,id',
            'languages.*.address' => 'required|string',
            'languages.*.description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $address = Address::create([
            'internal_name' => $request->internal_name,
            'country_id' => $request->country_id,
            'backward_compatibility' => $request->backward_compatibility,
        ]);

        // Attach languages with addresses and descriptions
        foreach ($request->languages as $languageData) {
            $address->languages()->attach($languageData['language_id'], [
                'address' => $languageData['address'],
                'description' => $languageData['description'] ?? null,
            ]);
        }

        return (new AddressResource($address->load('languages')))->response()->setStatusCode(201);
    }

    /**
     * Display the specified address.
     *
     * @return \App\Http\Resources\AddressResource
     */
    public function show(Address $address)
    {
        return new AddressResource($address->load('languages'));
    }

    /**
     * Update the specified address.
     *
     * @return \App\Http\Resources\AddressResource|\Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Address $address)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'prohibited',
            'internal_name' => 'required|string|unique:addresses,internal_name,'.$address->id,
            'country_id' => 'required|exists:countries,id',
            'languages' => 'array|min:1',
            'languages.*.language_id' => 'required_with:languages|exists:languages,id',
            'languages.*.address' => 'required_with:languages|string',
            'languages.*.description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $address->update([
            'internal_name' => $request->internal_name,
            'country_id' => $request->country_id,
            'backward_compatibility' => $request->backward_compatibility,
        ]);

        // Update languages if provided
        if ($request->has('languages')) {
            // First detach all existing languages
            $address->languages()->detach();

            // Attach new languages with addresses and descriptions
            foreach ($request->languages as $languageData) {
                $address->languages()->attach($languageData['language_id'], [
                    'address' => $languageData['address'],
                    'description' => $languageData['description'] ?? null,
                ]);
            }
        }

        return new AddressResource($address->load('languages'));
    }

    /**
     * Remove the specified address.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Address $address)
    {
        $address->delete();

        return response()->noContent();
    }
}
