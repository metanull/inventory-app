<?php

namespace App\Http\Controllers\Web;

use App\Enums\Permission;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\IndexPartnerRequest;
use App\Http\Requests\Web\StorePartnerRequest;
use App\Http\Requests\Web\UpdatePartnerRequest;
use App\Models\Country;
use App\Models\Partner;
use App\Services\Web\PartnerIndexQuery;
use App\Services\Web\PartnerShowPageData;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PartnerController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:'.Permission::VIEW_DATA->value)->only(['index', 'show']);
        $this->middleware('permission:'.Permission::CREATE_DATA->value)->only(['create', 'store']);
        $this->middleware('permission:'.Permission::UPDATE_DATA->value)->only(['edit', 'update', 'setMonument', 'removeMonument']);
        $this->middleware('permission:'.Permission::DELETE_DATA->value)->only(['destroy']);
    }

    public function index(IndexPartnerRequest $request, PartnerIndexQuery $partnerIndexQuery): View
    {
        $listState = $request->listState();

        return view('partners.index', [
            'partners' => $partnerIndexQuery->paginate($listState),
            'listState' => $listState,
        ]);
    }

    public function show(Partner $partner, PartnerShowPageData $partnerShowPageData): View
    {
        return view('partners.show', array_merge(
            $partnerShowPageData->build($partner),
            compact('partner')
        ));
    }

    public function create(): View
    {
        $countries = Country::query()->orderBy('internal_name')->get(['id', 'internal_name']);

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
        $countries = Country::query()->orderBy('internal_name')->get(['id', 'internal_name']);

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

    public function setMonument(Request $request, Partner $partner): RedirectResponse
    {
        $request->validate([
            'monument_item_id' => ['required', 'exists:items,id'],
        ]);

        $partner->update(['monument_item_id' => $request->monument_item_id]);

        return redirect()->route('partners.show', $partner)
            ->with('success', 'Monument item set successfully');
    }

    public function removeMonument(Partner $partner): RedirectResponse
    {
        $partner->update(['monument_item_id' => null]);

        return redirect()->route('partners.show', $partner)
            ->with('success', 'Monument item removed successfully');
    }
}
