<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\IndexAddressRequest;
use App\Http\Requests\Api\ShowAddressRequest;
use App\Http\Requests\Api\StoreAddressRequest;
use App\Http\Requests\Api\UpdateAddressRequest;
use App\Http\Resources\AddressResource;
use App\Models\Address;
use App\Support\Includes\AllowList;
use App\Support\Includes\IncludeParser;
use Illuminate\Http\JsonResponse;

class AddressController extends Controller
{
    /**
     * Display a listing of addresses.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(IndexAddressRequest $request)
    {
        $includes = $request->getIncludeParams();
        $pagination = $request->getPaginationParams();

        $defaults = ['translations'];
        $with = array_values(array_unique(array_merge($defaults, $includes)));
        $query = Address::query()->with($with);

        $paginator = $query->paginate(
            $pagination['per_page'],
            ['*'],
            'page',
            $pagination['page']
        );

        return AddressResource::collection($paginator);
    }

    /**
     * Store a newly created address.
     */
    public function store(StoreAddressRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $address = Address::create([
            'internal_name' => $validated['internal_name'],
            'country_id' => $validated['country_id'],
            'backward_compatibility' => $validated['backward_compatibility'] ?? null,
        ]);

        // Create translations if provided
        if (isset($validated['translations'])) {
            foreach ($validated['translations'] as $translationData) {
                $address->translations()->create([
                    'language_id' => $translationData['language_id'],
                    'address' => $translationData['address'],
                    'description' => $translationData['description'] ?? null,
                ]);
            }
        }

        $requested = IncludeParser::fromRequest($request, AllowList::for('address'));
        $defaults = ['translations'];
        $address->load(array_values(array_unique(array_merge($defaults, $requested))));

        return (new AddressResource($address))->response()->setStatusCode(201);
    }

    /**
     * Display the specified address.
     *
     * @return \App\Http\Resources\AddressResource
     */
    public function show(ShowAddressRequest $request, Address $address)
    {
        $includes = $request->getIncludeParams();
        if (! empty($includes)) {
            $address->load($includes);
        }

        return new AddressResource($address);
    }

    /**
     * Update the specified address.
     *
     * @return \App\Http\Resources\AddressResource
     */
    public function update(UpdateAddressRequest $request, Address $address)
    {
        $validated = $request->validated();

        $address->update([
            'internal_name' => $validated['internal_name'],
            'country_id' => $validated['country_id'],
            'backward_compatibility' => $validated['backward_compatibility'] ?? null,
        ]);

        // Update translations if provided
        if (isset($validated['translations'])) {
            // Delete existing translations
            $address->translations()->delete();

            // Create new translations
            foreach ($validated['translations'] as $translationData) {
                $address->translations()->create([
                    'language_id' => $translationData['language_id'],
                    'address' => $translationData['address'],
                    'description' => $translationData['description'] ?? null,
                ]);
            }
        }

        $requested = IncludeParser::fromRequest($request, AllowList::for('address'));
        $defaults = ['translations'];
        $address->load(array_values(array_unique(array_merge($defaults, $requested))));

        return new AddressResource($address);
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
