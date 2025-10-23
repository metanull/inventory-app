<?php

namespace App\Http\Controllers\Web;

use App\Enums\Permission;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\StoreItemRequest;
use App\Http\Requests\Web\UpdateItemRequest;
use App\Models\Item;
use App\Support\Web\SearchAndPaginate;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    use SearchAndPaginate;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:'.Permission::VIEW_DATA->value)->only(['index', 'show']);
        $this->middleware('permission:'.Permission::CREATE_DATA->value)->only(['create', 'store']);
        $this->middleware('permission:'.Permission::UPDATE_DATA->value)->only(['edit', 'update', 'attachTag', 'detachTag']);
        $this->middleware('permission:'.Permission::DELETE_DATA->value)->only(['destroy']);
    }

    public function index(Request $request): View
    {
        [$items, $search] = $this->searchAndPaginate(Item::query(), $request);

        return view('items.index', compact('items', 'search'));
    }

    public function show(Item $item): View
    {
        // Load translations with their relationships
        $item->load(['translations.context', 'translations.language']);

        // Get default context and language IDs
        $defaultContextId = \App\Models\Context::where('is_default', true)->value('id');
        $defaultLanguageId = \App\Models\Language::where('is_default', true)->value('id');

        // Group translations by context, with default context first
        // Within each group, order by language with default language first
        $translationsByContext = $item->translations
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

        return view('items.show', compact('item', 'translationsByContext'));
    }

    public function create(): View
    {
        return view('items.create');
    }

    public function store(StoreItemRequest $request): RedirectResponse
    {
        $item = Item::create($request->validated());

        return redirect()->route('items.show', $item)
            ->with('success', 'Item created successfully');
    }

    public function edit(Item $item): View
    {
        return view('items.edit', compact('item'));
    }

    public function update(UpdateItemRequest $request, Item $item): RedirectResponse
    {
        $item->update($request->validated());

        return redirect()->route('items.show', $item)
            ->with('success', 'Item updated successfully');
    }

    public function destroy(Item $item): RedirectResponse
    {
        $item->delete();

        return redirect()->route('items.index')
            ->with('success', 'Item deleted successfully');
    }

    public function attachTag(Request $request, Item $item): RedirectResponse
    {
        $request->validate([
            'tag_id' => ['required', 'exists:tags,id'],
        ]);

        // Attach the tag if not already attached
        if (! $item->tags()->where('tag_id', $request->tag_id)->exists()) {
            $item->tags()->attach($request->tag_id);
            $message = 'Tag attached successfully';
        } else {
            $message = 'Tag is already attached to this item';
        }

        return redirect()->route('items.show', $item)
            ->with('success', $message);
    }

    public function detachTag(Item $item, \App\Models\Tag $tag): RedirectResponse
    {
        if ($item->tags()->where('tag_id', $tag->id)->exists()) {
            $item->tags()->detach($tag->id);
            $message = 'Tag removed successfully';
        } else {
            $message = 'Tag was not attached to this item';
        }

        return redirect()->route('items.show', $item)
            ->with('success', $message);
    }
}
