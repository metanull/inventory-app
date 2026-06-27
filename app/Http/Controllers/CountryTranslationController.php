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
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class CountryTranslationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexCountryTranslationRequest $request): AnonymousResourceCollection
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
     */
    public function store(StoreCountryTranslationRequest $request): CountryTranslationResource
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
    public function show(ShowCountryTranslationRequest $request, CountryTranslation $countryTranslation): CountryTranslationResource
    {
        $includes = $request->getIncludeParams();
        $countryTranslation->load($includes);

        return new CountryTranslationResource($countryTranslation);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCountryTranslationRequest $request, CountryTranslation $countryTranslation): CountryTranslationResource
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
    public function destroy(CountryTranslation $countryTranslation): Response
    {
        $countryTranslation->delete();

        return response()->noContent();
    }
}
