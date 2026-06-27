<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\IndexCountryRequest;
use App\Http\Requests\Api\ShowCountryRequest;
use App\Http\Requests\Api\StoreCountryRequest;
use App\Http\Requests\Api\UpdateCountryRequest;
use App\Http\Resources\CountryResource;
use App\Models\Country;
use App\Support\Includes\AllowList;
use App\Support\Includes\IncludeParser;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class CountryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexCountryRequest $request): AnonymousResourceCollection
    {
        $includes = $request->getIncludeParams();
        $pagination = $request->getPaginationParams();

        $query = Country::query()->with($includes);
        $paginator = $query->paginate(
            $pagination['per_page'],
            ['*'],
            'page',
            $pagination['page']
        );

        return CountryResource::collection($paginator);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCountryRequest $request): CountryResource
    {
        $validated = $request->validated();
        $country = Country::create($validated);
        $country->refresh();
        $includes = IncludeParser::fromRequest($request, AllowList::for('country'));
        $country->load($includes);

        return new CountryResource($country);
    }

    /**
     * Display the specified resource.
     */
    public function show(ShowCountryRequest $request, Country $country): CountryResource
    {
        $includes = $request->getIncludeParams();
        $country->load($includes);

        return new CountryResource($country);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCountryRequest $request, Country $country): CountryResource
    {
        $validated = $request->validated();
        $country->update($validated);
        $country->refresh();
        $includes = IncludeParser::fromRequest($request, AllowList::for('country'));
        $country->load($includes);

        return new CountryResource($country);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Country $country): Response
    {
        $country->delete();

        return response()->noContent();
    }
}
