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

    /**
     * Display a listing of collection translations.
     */
    public function index(Request $request): View
    {
        $query = CollectionTranslation::with(['collection', 'language', 'context']);

        // Apply search if provided
        $search = $request->input('q');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('collection', function ($collectionQuery) use ($search) {
                        $collectionQuery->where('internal_name', 'like', "%{$search}%")
                            ->orWhere('id', 'like', "%{$search}%");
                    });
            });
        }

        $perPage = $request->input('perPage', 15);
        $collectionTranslations = $query->orderByDesc('created_at')->paginate($perPage)->withQueryString();

        return view('collection-translations.index', compact('collectionTranslations', 'search'));
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
