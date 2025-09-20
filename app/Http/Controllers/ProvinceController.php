<?php

namespace App\Http\Controllers;

use App\Http\Requests\Province\DestroyProvinceRequest;
use App\Http\Requests\Province\IndexProvinceRequest;
use App\Http\Requests\Province\ShowProvinceRequest;
use App\Http\Requests\Province\StoreProvinceRequest;
use App\Http\Requests\Province\UpdateProvinceRequest;
use App\Http\Resources\ProvinceResource;
use App\Models\Province;
use App\Support\Includes\AllowList;
use App\Support\Includes\IncludeParser;
use App\Support\Pagination\PaginationParams;
use Illuminate\Http\JsonResponse;

class ProvinceController extends Controller
{
    /**
     * Display a listing of provinces.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(IndexProvinceRequest $request)
    {
        $validatedData = $request->validated();
        $includes = IncludeParser::fromRequest($request, AllowList::for('province'));
        $pagination = PaginationParams::fromRequest($request);

        $defaults = ['translations'];
        $with = array_values(array_unique(array_merge($defaults, $includes)));
        $query = Province::query()->with($with);

        $paginator = $query->paginate(
            $pagination['per_page'],
            ['*'],
            'page',
            $pagination['page']
        );

        return ProvinceResource::collection($paginator);
    }

    /**
     * Store a newly created province.
     */
    public function store(StoreProvinceRequest $request): JsonResponse
    {
        $validatedData = $request->validated();

        $province = Province::create([
            'internal_name' => $validatedData['internal_name'],
            'country_id' => $validatedData['country_id'],
            'backward_compatibility' => $validatedData['backward_compatibility'] ?? null,
        ]);

        // Create translations
        foreach ($validatedData['translations'] as $translationData) {
            $province->translations()->create([
                'language_id' => $translationData['language_id'],
                'name' => $translationData['name'],
            ]);
        }

        $requested = IncludeParser::fromRequest($request, AllowList::for('province'));
        $defaults = ['translations'];
        $province->load(array_values(array_unique(array_merge($defaults, $requested))));

        return (new ProvinceResource($province))->response()->setStatusCode(201);
    }

    /**
     * Display the specified province.
     *
     * @return \App\Http\Resources\ProvinceResource
     */
    public function show(ShowProvinceRequest $request, Province $province)
    {
        $validatedData = $request->validated();
        $includes = IncludeParser::fromRequest($request, AllowList::for('province'));
        if (! empty($includes)) {
            $province->load($includes);
        }

        return new ProvinceResource($province);
    }

    /**
     * Update the specified province.
     *
     * @return \App\Http\Resources\ProvinceResource|\Illuminate\Http\JsonResponse
     */
    public function update(UpdateProvinceRequest $request, Province $province)
    {
        $validatedData = $request->validated();

        $province->update([
            'internal_name' => $validatedData['internal_name'] ?? $province->internal_name,
            'country_id' => $validatedData['country_id'] ?? $province->country_id,
            'backward_compatibility' => $validatedData['backward_compatibility'] ?? $province->backward_compatibility,
        ]);

        // Update translations if provided
        if (isset($validatedData['translations'])) {
            // Delete existing translations
            $province->translations()->delete();

            // Create new translations
            foreach ($validatedData['translations'] as $translationData) {
                $province->translations()->create([
                    'language_id' => $translationData['language_id'],
                    'name' => $translationData['name'],
                ]);
            }
        }

        $requested = IncludeParser::fromRequest($request, AllowList::for('province'));
        $defaults = ['translations'];
        $province->load(array_values(array_unique(array_merge($defaults, $requested))));

        return new ProvinceResource($province);
    }

    /**
     * Remove the specified province.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(DestroyProvinceRequest $request, Province $province)
    {
        $validatedData = $request->validated();
        $province->delete();

        return response()->noContent();
    }
}
