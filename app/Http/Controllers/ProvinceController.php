<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\IndexProvinceRequest;
use App\Http\Requests\Api\ShowProvinceRequest;
use App\Http\Requests\Api\StoreProvinceRequest;
use App\Http\Requests\Api\UpdateProvinceRequest;
use App\Http\Resources\ProvinceResource;
use App\Models\Province;
use App\Support\Includes\AllowList;
use App\Support\Includes\IncludeParser;
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
        $includes = $request->getIncludeParams();
        $pagination = $request->getPaginationParams();

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
        $validated = $request->validated();

        $province = Province::create([
            'internal_name' => $validated['internal_name'],
            'country_id' => $validated['country_id'],
            'backward_compatibility' => $validated['backward_compatibility'] ?? null,
        ]);

        // Create translations
        foreach ($validated['translations'] as $translationData) {
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
        $includes = $request->getIncludeParams();
        if (! empty($includes)) {
            $province->load($includes);
        }

        return new ProvinceResource($province);
    }

    /**
     * Update the specified province.
     *
     * @return \App\Http\Resources\ProvinceResource
     */
    public function update(UpdateProvinceRequest $request, Province $province)
    {
        $validated = $request->validated();

        $province->update([
            'internal_name' => $validated['internal_name'],
            'country_id' => $validated['country_id'],
            'backward_compatibility' => $validated['backward_compatibility'] ?? null,
        ]);

        // Update translations if provided
        if (isset($validated['translations'])) {
            // Delete existing translations
            $province->translations()->delete();

            // Create new translations
            foreach ($validated['translations'] as $translationData) {
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
    public function destroy(Province $province)
    {
        $province->delete();

        return response()->noContent();
    }
}
