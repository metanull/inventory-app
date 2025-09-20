<?php

namespace App\Http\Controllers;

use App\Http\Requests\Country\IndexCountryRequest;
use App\Http\Requests\Country\ShowCountryRequest;
use App\Http\Requests\Country\StoreCountryRequest;
use App\Http\Requests\Country\UpdateCountryRequest;
use App\Http\Resources\CountryResource;
use App\Models\Country;
use App\Support\Includes\AllowList;
use App\Support\Includes\IncludeParser;
use App\Support\Pagination\PaginationParams;

class CountryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexCountryRequest $request)
    {
        $includes = IncludeParser::fromRequest($request, AllowList::for('country'));
        $pagination = PaginationParams::fromRequest($request);

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
    public function store(StoreCountryRequest $request)
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
    public function show(ShowCountryRequest $request, Country $country)
    {
        $includes = IncludeParser::fromRequest($request, AllowList::for('country'));
        $country->load($includes);

        return new CountryResource($country);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCountryRequest $request, Country $country)
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
    public function destroy(Country $country)
    {
        $country->delete();

        return response()->noContent();
    }
}
