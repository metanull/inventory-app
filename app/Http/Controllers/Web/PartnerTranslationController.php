<?php

namespace App\Http\Controllers\Web;

use App\Enums\Permission;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\StorePartnerTranslationRequest;
use App\Http\Requests\Web\UpdatePartnerTranslationRequest;
use App\Models\Context;
use App\Models\Language;
use App\Models\Partner;
use App\Models\PartnerTranslation;
use App\Support\Web\SearchAndPaginate;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PartnerTranslationController extends Controller
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

    /**
     * Display a listing of partner translations.
     */
    public function index(Request $request): View
    {
        $query = PartnerTranslation::with(['partner', 'language', 'context']);

        // Apply search if provided
        $search = $request->input('q');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('city_display', 'like', "%{$search}%")
                    ->orWhereHas('partner', function ($partnerQuery) use ($search) {
                        $partnerQuery->where('internal_name', 'like', "%{$search}%")
                            ->orWhere('id', 'like', "%{$search}%");
                    });
            });
        }

        $perPage = $request->input('perPage', 15);
        $partnerTranslations = $query->orderByDesc('created_at')->paginate($perPage)->withQueryString();

        return view('partner-translations.index', compact('partnerTranslations', 'search'));
    }

    /**
     * Show the form for creating a new partner translation.
     */
    public function create(Request $request): View
    {
        $partners = Partner::orderBy('internal_name')->get();
        $languages = Language::orderBy('internal_name')->get();
        $contexts = Context::orderBy('internal_name')->get();
        $defaultContext = Context::where('is_default', true)->first();
        $defaultLanguage = Language::where('is_default', true)->first();

        // Get partner_id from query parameter if provided (from partner show page)
        $selectedPartnerId = $request->input('partner_id');

        return view('partner-translations.create', compact('partners', 'languages', 'contexts', 'defaultContext', 'defaultLanguage', 'selectedPartnerId'));
    }

    /**
     * Store a newly created partner translation in storage.
     */
    public function store(StorePartnerTranslationRequest $request): RedirectResponse
    {
        $partnerTranslation = PartnerTranslation::create($request->validated());

        return redirect()
            ->route('partner-translations.show', $partnerTranslation)
            ->with('success', 'Partner translation created successfully');
    }

    /**
     * Display the specified partner translation.
     */
    public function show(PartnerTranslation $partnerTranslation): View
    {
        $partnerTranslation->load(['partner', 'language', 'context']);

        return view('partner-translations.show', compact('partnerTranslation'));
    }

    /**
     * Show the form for editing the specified partner translation.
     */
    public function edit(PartnerTranslation $partnerTranslation): View
    {
        $partnerTranslation->load(['partner', 'language', 'context']);

        $partners = Partner::orderBy('internal_name')->get();
        $languages = Language::orderBy('internal_name')->get();
        $contexts = Context::orderBy('internal_name')->get();

        return view('partner-translations.edit', compact('partnerTranslation', 'partners', 'languages', 'contexts'));
    }

    /**
     * Update the specified partner translation in storage.
     */
    public function update(UpdatePartnerTranslationRequest $request, PartnerTranslation $partnerTranslation): RedirectResponse
    {
        $partnerTranslation->update($request->validated());

        return redirect()
            ->route('partner-translations.show', $partnerTranslation)
            ->with('success', 'Partner translation updated successfully');
    }

    /**
     * Remove the specified partner translation from storage.
     */
    public function destroy(PartnerTranslation $partnerTranslation): RedirectResponse
    {
        $partnerTranslation->delete();

        return redirect()
            ->route('partner-translations.index')
            ->with('success', 'Partner translation deleted successfully');
    }
}
