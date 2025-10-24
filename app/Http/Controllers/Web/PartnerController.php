<?php

namespace App\Http\Controllers\Web;

use App\Enums\Permission;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\StorePartnerRequest;
use App\Http\Requests\Web\UpdatePartnerRequest;
use App\Models\Partner;
use App\Support\Web\SearchAndPaginate;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PartnerController extends Controller
{
    use SearchAndPaginate;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:'.Permission::VIEW_DATA->value)->only(['index', 'show']);
        $this->middleware('permission:'.Permission::CREATE_DATA->value)->only(['create', 'store']);
        $this->middleware('permission:'.Permission::UPDATE_DATA->value)->only(['edit', 'update']);
        $this->middleware('permission:'.Permission::DELETE_DATA->value)->only(['destroy']);
    }

    public function index(Request $request): View
    {
        [$partners, $search] = $this->searchAndPaginate(Partner::query()->with('country'), $request);

        return view('partners.index', compact('partners', 'search'));
    }

    public function show(Partner $partner): View
    {
        $partner->load('country', 'project', 'monumentItem');

        return view('partners.show', compact('partner'));
    }

    public function create(): View
    {
        $countries = \App\Models\Country::query()->orderBy('internal_name')->get(['id', 'internal_name']);

        return view('partners.create', compact('countries'));
    }

    public function store(StorePartnerRequest $request): RedirectResponse
    {
        $partner = Partner::create($request->validated());

        return redirect()->route('partners.show', $partner)->with('success', 'Partner created successfully');
    }

    public function edit(Partner $partner): View
    {
        $partner->load('country', 'project', 'monumentItem');
        $countries = \App\Models\Country::query()->orderBy('internal_name')->get(['id', 'internal_name']);

        return view('partners.edit', compact('partner', 'countries'));
    }

    public function update(UpdatePartnerRequest $request, Partner $partner): RedirectResponse
    {
        $partner->update($request->validated());

        return redirect()->route('partners.show', $partner)->with('success', 'Partner updated successfully');
    }

    public function destroy(Partner $partner): RedirectResponse
    {
        $partner->delete();

        return redirect()->route('partners.index')->with('success', 'Partner deleted successfully');
    }
}
