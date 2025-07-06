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
        $addresses = Address::with(['translations'])->get();

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
            'translations' => 'array|min:1',
            'translations.*.language_id' => 'required|exists:languages,id',
            'translations.*.address' => 'required|string',
            'translations.*.description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $address = Address::create([
            'internal_name' => $request->internal_name,
            'country_id' => $request->country_id,
            'backward_compatibility' => $request->backward_compatibility,
        ]);

        // Create translations if provided
        if ($request->has('translations')) {
            foreach ($request->translations as $translationData) {
                $address->translations()->create([
                    'language_id' => $translationData['language_id'],
                    'address' => $translationData['address'],
                    'description' => $translationData['description'] ?? null,
                ]);
            }
        }

        return (new AddressResource($address->load('translations')))->response()->setStatusCode(201);
    }

    /**
     * Display the specified address.
     *
     * @return \App\Http\Resources\AddressResource
     */
    public function show(Address $address)
    {
        return new AddressResource($address->load('translations'));
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
            'translations' => 'array|min:1',
            'translations.*.language_id' => 'required_with:translations|exists:languages,id',
            'translations.*.address' => 'required_with:translations|string',
            'translations.*.description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $address->update([
            'internal_name' => $request->internal_name,
            'country_id' => $request->country_id,
            'backward_compatibility' => $request->backward_compatibility,
        ]);

        // Update translations if provided
        if ($request->has('translations')) {
            // Delete existing translations
            $address->translations()->delete();

            // Create new translations
            foreach ($request->translations as $translationData) {
                $address->translations()->create([
                    'language_id' => $translationData['language_id'],
                    'address' => $translationData['address'],
                    'description' => $translationData['description'] ?? null,
                ]);
            }
        }

        return new AddressResource($address->load('translations'));
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
