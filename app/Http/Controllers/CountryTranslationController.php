<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\IndexCountryTranslationRequest;
use App\Http\Requests\Api\ShowCountryTranslationRequest;
use App\Http\Requests\Api\StoreCountryTranslationRequest;
use App\Http\Requests\Api\UpdateCountryTranslationRequest;
use App\Http\Resources\CountryTranslationResource;
use App\Models\CountryTranslation;
use App\Support\Includes\AllowList;
use App\Support\Includes\IncludeParser;

class CountryTranslationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexCountryTranslationRequest $request)
    {
        $includes = $request->getIncludeParams();
        $pagination = $request->getPaginationParams();

        $query = CountryTranslation::query()->with($includes);
        $paginator = $query->paginate(
            $pagination['per_page'],
            ['*'],
            'page',
            $pagination['page']
        );

        return CountryTranslationResource::collection($paginator);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return CountryTranslationResource
     */
    public function store(StoreCountryTranslationRequest $request)
    {
        $validated = $request->validated();
        $countryTranslation = CountryTranslation::create($validated);
        $countryTranslation->refresh();
        $includes = IncludeParser::fromRequest($request, AllowList::for('country_translation'));
        $countryTranslation->load($includes);

        return new CountryTranslationResource($countryTranslation);
    }

    /**
     * Display the specified resource.
     */
    public function show(ShowCountryTranslationRequest $request, CountryTranslation $countryTranslation)
    {
        $includes = $request->getIncludeParams();
        $countryTranslation->load($includes);

        return new CountryTranslationResource($countryTranslation);
    }

    /**
     * Update the specified resource in storage.
     *
     * @return CountryTranslationResource
     */
    public function update(UpdateCountryTranslationRequest $request, CountryTranslation $countryTranslation)
    {
        $validated = $request->validated();
        $countryTranslation->update($validated);
        $countryTranslation->refresh();
        $includes = IncludeParser::fromRequest($request, AllowList::for('country_translation'));
        $countryTranslation->load($includes);

        return new CountryTranslationResource($countryTranslation);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CountryTranslation $countryTranslation)
    {
        $countryTranslation->delete();

        return response()->noContent();
    }
}
