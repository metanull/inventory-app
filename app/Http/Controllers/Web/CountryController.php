<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\StoreCountryRequest;
use App\Http\Requests\Web\UpdateCountryRequest;
use App\Models\Country;
use App\Support\Web\SearchAndPaginate;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CountryController extends Controller
{
    use SearchAndPaginate;

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request): View
    {
        /** @var LengthAwarePaginator $countries */
        [$countries, $search] = $this->searchAndPaginate(Country::query(), $request);

        return view('countries.index', compact('countries', 'search'));
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
