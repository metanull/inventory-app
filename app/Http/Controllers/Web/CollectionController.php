<?php

namespace App\Http\Controllers\Web;

use App\Enums\Permission;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\StoreCollectionRequest;
use App\Http\Requests\Web\UpdateCollectionRequest;
use App\Models\Collection;
use App\Models\Context;
use App\Models\Language;
use App\Support\Web\SearchAndPaginate;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CollectionController extends Controller
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
        [$collections, $search] = $this->searchAndPaginate(Collection::query()->with(['context', 'language']), $request);

        return view('collections.index', compact('collections', 'search'));
    }

    public function show(Collection $collection): View
    {
        $collection->load(['context', 'language']);

        // Get default context and language IDs
        $defaultContextId = \App\Models\Context::where('is_default', true)->value('id');
        $defaultLanguageId = \App\Models\Language::where('is_default', true)->value('id');

        // Group translations by context, with default context first
        // Within each group, order by language with default language first
        $translationsByContext = $collection->translations
            ->sortBy(function ($translation) use ($defaultLanguageId) {
                // Default language first (0), then others (1)
                return $translation->language_id === $defaultLanguageId ? 0 : 1;
            })
            ->groupBy('context_id')
            ->sortBy(function ($group, $contextId) use ($defaultContextId) {
                // Null context last
                if ($contextId === null) {
                    return PHP_INT_MAX;
                }
                // Default context first
                if ($contextId === $defaultContextId) {
                    return 0;
                }

                // Others sorted by ID
                return $contextId;
            });

        return view('collections.show', compact('collection', 'translationsByContext'));
    }

    public function create(): View
    {
        $contexts = Context::query()->orderBy('internal_name')->get(['id', 'internal_name']);
        $languages = Language::query()->orderBy('id')->get(['id', 'internal_name']);

        return view('collections.create', compact('contexts', 'languages'));
    }

    public function store(StoreCollectionRequest $request): RedirectResponse
    {
        $collection = Collection::create($request->validated());

        return redirect()->route('collections.show', $collection)->with('success', 'Collection created successfully');
    }

    public function edit(Collection $collection): View
    {
        $collection->load(['context', 'language']);
        $contexts = Context::query()->orderBy('internal_name')->get(['id', 'internal_name']);
        $languages = Language::query()->orderBy('id')->get(['id', 'internal_name']);

        return view('collections.edit', compact('collection', 'contexts', 'languages'));
    }

    public function update(UpdateCollectionRequest $request, Collection $collection): RedirectResponse
    {
        $collection->update($request->validated());

        return redirect()->route('collections.show', $collection)->with('success', 'Collection updated successfully');
    }

    public function destroy(Collection $collection): RedirectResponse
    {
        $collection->delete();

        return redirect()->route('collections.index')->with('success', 'Collection deleted successfully');
    }
}
