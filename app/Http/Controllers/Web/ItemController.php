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
        $item->load([
            'translations.context',
            'translations.language',
            'outgoingLinks.target.itemImages',
            'outgoingLinks.context',
            'incomingLinks.source.itemImages',
            'incomingLinks.context',
            'parent.itemImages',
            'children.itemImages',
        ]);

        return view('items.show', compact('item'));
    }

    public function showModern(Item $item): View
    {
        // Load translations with their relationships
        $item->load([
            'translations.context',
            'translations.language',
            'outgoingLinks.target.itemImages',
            'outgoingLinks.context',
            'incomingLinks.source.itemImages',
            'incomingLinks.context',
            'parent.itemImages',
            'children.itemImages',
        ]);

        return view('items.show-modern', compact('item'));
    }

    public function create(Request $request): View
    {
        $parentId = $request->query('parent_id');
        $parent = null;

        if ($parentId) {
            $parent = Item::find($parentId);
            if (! $parent) {
                abort(404, 'Parent item not found');
            }
        }

        return view('items.create', compact('parent'));
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

    public function setParent(Request $request, Item $item): RedirectResponse
    {
        $request->validate([
            'parent_id' => ['required', 'exists:items,id'],
        ]);

        // Prevent item from being its own parent
        if ($request->parent_id === $item->id) {
            return redirect()->back()
                ->withErrors(['parent_id' => 'An item cannot be its own parent'])
                ->withInput();
        }

        // Prevent circular references by checking if the potential parent
        // has this item anywhere in its ancestry chain
        $potentialParent = Item::findOrFail($request->parent_id);
        $ancestor = $potentialParent;
        while ($ancestor->parent_id !== null) {
            if ($ancestor->parent_id === $item->id) {
                return redirect()->back()
                    ->withErrors(['parent_id' => 'Cannot create circular parent relationship'])
                    ->withInput();
            }
            $ancestor = Item::find($ancestor->parent_id);
            if (! $ancestor) {
                break;
            }
        }

        $item->update(['parent_id' => $request->parent_id]);

        return redirect()->route('items.show', $item)
            ->with('success', 'Parent set successfully');
    }

    public function removeParent(Item $item): RedirectResponse
    {
        $item->update(['parent_id' => null]);

        return redirect()->route('items.show', $item)
            ->with('success', 'Parent relationship removed successfully');
    }
}
