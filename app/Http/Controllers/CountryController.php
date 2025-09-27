<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\IndexCountryRequest;
use App\Http\Requests\Api\ShowCountryRequest;
use App\Http\Resources\CountryResource;
use App\Models\Country;
use App\Support\Includes\AllowList;
use App\Support\Includes\IncludeParser;
use Illuminate\Http\Request;

class CountryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexCountryRequest $request)
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
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|string|size:3|unique:countries,id',
            'internal_name' => 'required|string',
            'backward_compatibility' => 'nullable|string|size:2',
        ]);
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
        $includes = $request->getIncludeParams();
        $country->load($includes);

        return new CountryResource($country);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Country $country)
    {
        $validated = $request->validate([
            /** @ignoreParam */
            'id' => 'prohibited',
            'internal_name' => 'required|string',
            'backward_compatibility' => 'nullable|string|size:2',
        ]);
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
