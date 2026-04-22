<?php

namespace App\Http\Controllers\Web;

use App\Enums\Permission;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\IndexCountryRequest;
use App\Http\Requests\Web\StoreCountryRequest;
use App\Http\Requests\Web\UpdateCountryRequest;
use App\Models\Country;
use App\Services\Web\CountryIndexQuery;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class CountryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:'.Permission::VIEW_DATA->value)->only(['index', 'show']);
        $this->middleware('permission:'.Permission::CREATE_DATA->value)->only(['create', 'store']);
        $this->middleware('permission:'.Permission::UPDATE_DATA->value)->only(['edit', 'update']);
        $this->middleware('permission:'.Permission::DELETE_DATA->value)->only(['destroy']);
    }

    public function index(IndexCountryRequest $request, CountryIndexQuery $countryIndexQuery): View
    {
        $listState = $request->listState();

        return view('countries.index', [
            'countries' => $countryIndexQuery->paginate($listState),
            'listState' => $listState,
        ]);
    }

    public function show(Country $country): View
    {
        return view('countries.show', compact('country'));
    }

    public function create(): View
    {
        return view('countries.create');
    }

    public function store(StoreCountryRequest $request): RedirectResponse
    {
        $country = Country::create($request->validated());

        return redirect()->route('countries.show', $country)->with('success', 'Country created successfully');
    }

    public function edit(Country $country): View
    {
        return view('countries.edit', compact('country'));
    }

    public function update(UpdateCountryRequest $request, Country $country): RedirectResponse
    {
        $country->update($request->validated());

        return redirect()->route('countries.show', $country)->with('success', 'Country updated successfully');
    }

    public function destroy(Country $country): RedirectResponse
    {
        $country->delete();

        return redirect()->route('countries.index')->with('success', 'Country deleted successfully');
    }
}
