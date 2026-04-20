<?php

namespace App\Http\Controllers\Web;

use App\Enums\Permission;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\StoreCollectionTranslationRequest;
use App\Http\Requests\Web\UpdateCollectionTranslationRequest;
use App\Models\Collection;
use App\Models\CollectionTranslation;
use App\Models\Context;
use App\Models\Language;
use App\Support\Web\SearchAndPaginate;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CollectionTranslationController extends Controller
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
     * Display a listing of collection translations.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('q', ''));
        $contextFilter = (string) $request->query('context', '');
        $languageFilter = (string) $request->query('language', '');
        $sort = (string) $request->query('sort', 'created_at');
        $dir = strtolower((string) $request->query('dir', 'desc'));

        $allowedSortFields = ['title', 'created_at'];
        if (! in_array($sort, $allowedSortFields, true)) {
            $sort = 'created_at';
        }
        if (! in_array($dir, ['asc', 'desc'], true)) {
            $dir = 'desc';
        }

        $query = CollectionTranslation::with(['collection', 'language', 'context']);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('collection', function ($collectionQuery) use ($search) {
                        $collectionQuery->where('internal_name', 'like', "%{$search}%")
                            ->orWhere('id', 'like', "%{$search}%");
                    });
            });
        }

        if ($contextFilter === 'default') {
            $defaultContext = Context::where('is_default', true)->first();
            if ($defaultContext) {
                $query->where('context_id', $defaultContext->id);
            }
        } elseif ($contextFilter !== '') {
            $query->where('context_id', $contextFilter);
        }

        if ($languageFilter === 'default') {
            $defaultLanguage = Language::where('is_default', true)->first();
            if ($defaultLanguage) {
                $query->where('language_id', $defaultLanguage->id);
            }
        } elseif ($languageFilter !== '') {
            $query->where('language_id', $languageFilter);
        }

        $perPage = $this->resolvePerPage($request);
        $collectionTranslations = $query->orderBy($sort, $dir)->paginate($perPage)->withQueryString();

        $contexts = Context::orderBy('internal_name')->get();
        $languages = Language::orderBy('internal_name')->get();

        return view('collection-translations.index', compact(
            'collectionTranslations', 'search', 'sort', 'dir', 'contextFilter', 'languageFilter', 'contexts', 'languages',
        ));
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
}
