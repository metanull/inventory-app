<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\IndexAddressRequest;
use App\Http\Requests\Api\ShowAddressRequest;
use App\Http\Resources\AddressResource;
use App\Models\Address;
use App\Support\Includes\AllowList;
use App\Support\Includes\IncludeParser;
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
