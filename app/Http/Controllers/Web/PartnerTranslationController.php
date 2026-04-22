<?php

namespace App\Http\Controllers\Web;

use App\Enums\Permission;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\IndexPartnerTranslationRequest;
use App\Http\Requests\Web\StorePartnerTranslationRequest;
use App\Http\Requests\Web\UpdatePartnerTranslationRequest;
use App\Models\Context;
use App\Models\Language;
use App\Models\Partner;
use App\Models\PartnerTranslation;
use App\Services\Web\PartnerTranslationIndexQuery;
use App\Support\Web\Lists\ListState;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PartnerTranslationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:'.Permission::VIEW_DATA->value)->only(['index', 'show']);
        $this->middleware('permission:'.Permission::CREATE_DATA->value)->only(['create', 'store']);
        $this->middleware('permission:'.Permission::UPDATE_DATA->value)->only(['edit', 'update']);
        $this->middleware('permission:'.Permission::DELETE_DATA->value)->only(['destroy']);
    }

    public function index(IndexPartnerTranslationRequest $request, PartnerTranslationIndexQuery $partnerTranslationIndexQuery): View
    {
        $listState = $request->listState();
        $partner = Partner::findOrFail($listState->filters['partner_id']);

        return view('partner-translations.index', [
            'partnerTranslations' => $partnerTranslationIndexQuery->paginate($listState),
            'listState' => $listState,
            'partner' => $partner,
            'selectedLanguage' => $this->resolveSelectedLanguage($listState),
            'selectedContext' => $this->resolveSelectedContext($listState),
        ]);
    }

    /**
     * Show the form for creating a new partner translation.
     */
    public function create(Request $request): View
    {
        $defaultContext = Context::where('is_default', true)->first();
        $defaultLanguage = Language::where('is_default', true)->first();

        // Get partner_id from query parameter if provided (from partner show page)
        $selectedPartnerId = $request->input('partner_id');

        return view('partner-translations.create', compact('defaultContext', 'defaultLanguage', 'selectedPartnerId'));
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

        return view('partner-translations.edit', compact('partnerTranslation'));
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

    private function resolveSelectedLanguage(ListState $listState): ?Language
    {
        $languageId = $listState->filters['language'] ?? null;

        if (! is_string($languageId) || $languageId === '') {
            return null;
        }

        return Language::query()->select('id', 'internal_name')->find($languageId);
    }

    private function resolveSelectedContext(ListState $listState): ?Context
    {
        $contextId = $listState->filters['context'] ?? null;

        if (! is_string($contextId) || $contextId === '') {
            return null;
        }

        return Context::query()->select('id', 'internal_name')->find($contextId);
    }
}
