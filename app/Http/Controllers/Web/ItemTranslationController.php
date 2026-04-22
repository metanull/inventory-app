<?php

namespace App\Http\Controllers\Web;

use App\Enums\Permission;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\IndexItemTranslationRequest;
use App\Http\Requests\Web\StoreItemTranslationRequest;
use App\Http\Requests\Web\UpdateItemTranslationRequest;
use App\Models\Context;
use App\Models\Item;
use App\Models\ItemTranslation;
use App\Models\Language;
use App\Services\Web\ItemTranslationIndexQuery;
use App\Support\Web\Lists\ListState;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ItemTranslationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:'.Permission::VIEW_DATA->value)->only(['index', 'show']);
        $this->middleware('permission:'.Permission::CREATE_DATA->value)->only(['create', 'store']);
        $this->middleware('permission:'.Permission::UPDATE_DATA->value)->only(['edit', 'update']);
        $this->middleware('permission:'.Permission::DELETE_DATA->value)->only(['destroy']);
    }

    public function index(IndexItemTranslationRequest $request, ItemTranslationIndexQuery $itemTranslationIndexQuery): View
    {
        $listState = $request->listState();
        $item = Item::findOrFail($listState->filters['item_id']);

        return view('item-translations.index', [
            'itemTranslations' => $itemTranslationIndexQuery->paginate($listState),
            'listState' => $listState,
            'item' => $item,
            'selectedLanguage' => $this->resolveSelectedLanguage($listState),
            'selectedContext' => $this->resolveSelectedContext($listState),
        ]);
    }

    /**
     * Show the form for creating a new item translation.
     */
    public function create(Request $request): View
    {
        $defaultContext = Context::where('is_default', true)->first();

        // Get item_id from query parameter if provided (from item show page)
        $selectedItemId = $request->input('item_id');

        return view('item-translations.create', compact('defaultContext', 'selectedItemId'));
    }

    /**
     * Store a newly created item translation in storage.
     */
    public function store(StoreItemTranslationRequest $request): RedirectResponse
    {
        $itemTranslation = ItemTranslation::create($request->validated());

        return redirect()
            ->route('item-translations.show', $itemTranslation)
            ->with('success', 'Item translation created successfully');
    }

    /**
     * Display the specified item translation.
     */
    public function show(ItemTranslation $itemTranslation): View
    {
        $itemTranslation->load(['item', 'language', 'context', 'author', 'textCopyEditor', 'translator', 'translationCopyEditor']);

        return view('item-translations.show', compact('itemTranslation'));
    }

    /**
     * Show the form for editing the specified item translation.
     */
    public function edit(ItemTranslation $itemTranslation): View
    {
        $itemTranslation->load(['item', 'language', 'context']);

        return view('item-translations.edit', compact('itemTranslation'));
    }

    /**
     * Update the specified item translation in storage.
     */
    public function update(UpdateItemTranslationRequest $request, ItemTranslation $itemTranslation): RedirectResponse
    {
        $itemTranslation->update($request->validated());

        return redirect()
            ->route('item-translations.show', $itemTranslation)
            ->with('success', 'Item translation updated successfully');
    }

    /**
     * Remove the specified item translation from storage.
     */
    public function destroy(ItemTranslation $itemTranslation): RedirectResponse
    {
        $itemTranslation->delete();

        return redirect()
            ->route('item-translations.index')
            ->with('success', 'Item translation deleted successfully');
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
