<?php

namespace App\Http\Controllers\Web;

use App\Enums\Permission;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\IndexCollectionTranslationRequest;
use App\Http\Requests\Web\StoreCollectionTranslationRequest;
use App\Http\Requests\Web\UpdateCollectionTranslationRequest;
use App\Models\Collection;
use App\Models\CollectionTranslation;
use App\Models\Context;
use App\Models\Language;
use App\Services\Web\CollectionTranslationIndexQuery;
use App\Support\Web\Lists\ListState;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CollectionTranslationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:'.Permission::VIEW_DATA->value)->only(['index', 'show']);
        $this->middleware('permission:'.Permission::CREATE_DATA->value)->only(['create', 'store']);
        $this->middleware('permission:'.Permission::UPDATE_DATA->value)->only(['edit', 'update']);
        $this->middleware('permission:'.Permission::DELETE_DATA->value)->only(['destroy']);
    }

    public function index(IndexCollectionTranslationRequest $request, CollectionTranslationIndexQuery $collectionTranslationIndexQuery): View
    {
        $listState = $request->listState();
        $collection = Collection::findOrFail($listState->filters['collection_id']);

        return view('collection-translations.index', [
            'collectionTranslations' => $collectionTranslationIndexQuery->paginate($listState),
            'listState' => $listState,
            'collection' => $collection,
            'selectedLanguage' => $this->resolveSelectedLanguage($listState),
            'selectedContext' => $this->resolveSelectedContext($listState),
        ]);
    }

    /**
     * Show the form for creating a new collection translation.
     */
    public function create(Request $request): View
    {
        $collections = Collection::orderBy('internal_name')->get();
        $languages = Language::orderBy('internal_name')->get();
        $contexts = Context::orderBy('internal_name')->get();
        $defaultContext = Context::where('is_default', true)->first();

        // Get collection_id from query parameter if provided (from collection show page)
        $selectedCollectionId = $request->input('collection_id');

        return view('collection-translations.create', compact('collections', 'languages', 'contexts', 'defaultContext', 'selectedCollectionId'));
    }

    /**
     * Store a newly created collection translation in storage.
     */
    public function store(StoreCollectionTranslationRequest $request): RedirectResponse
    {
        $collectionTranslation = CollectionTranslation::create($request->validated());

        return redirect()
            ->route('collection-translations.show', $collectionTranslation)
            ->with('success', 'Collection translation created successfully');
    }

    /**
     * Display the specified collection translation.
     */
    public function show(CollectionTranslation $collectionTranslation): View
    {
        $collectionTranslation->load(['collection', 'language', 'context']);

        return view('collection-translations.show', compact('collectionTranslation'));
    }

    /**
     * Show the form for editing the specified collection translation.
     */
    public function edit(CollectionTranslation $collectionTranslation): View
    {
        $collectionTranslation->load(['collection', 'language', 'context']);

        $collections = Collection::orderBy('internal_name')->get();
        $languages = Language::orderBy('internal_name')->get();
        $contexts = Context::orderBy('internal_name')->get();

        return view('collection-translations.edit', compact('collectionTranslation', 'collections', 'languages', 'contexts'));
    }

    /**
     * Update the specified collection translation in storage.
     */
    public function update(UpdateCollectionTranslationRequest $request, CollectionTranslation $collectionTranslation): RedirectResponse
    {
        $collectionTranslation->update($request->validated());

        return redirect()
            ->route('collection-translations.show', $collectionTranslation)
            ->with('success', 'Collection translation updated successfully');
    }

    /**
     * Remove the specified collection translation from storage.
     */
    public function destroy(CollectionTranslation $collectionTranslation): RedirectResponse
    {
        $collectionTranslation->delete();

        return redirect()
            ->route('collection-translations.index')
            ->with('success', 'Collection translation deleted successfully');
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
