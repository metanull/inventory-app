<?php

namespace App\Http\Controllers\Web;

use App\Enums\Permission;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\StoreItemItemLinkRequest;
use App\Http\Requests\Web\UpdateItemItemLinkRequest;
use App\Models\Context;
use App\Models\Item;
use App\Models\ItemItemLink;
use App\Support\Web\SearchAndPaginate;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ItemItemLinkController extends Controller
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
     * Display a listing of item-item links for an item.
     */
    public function index(Item $item): View
    {
        return view('item-links.index', compact('item'));
    }

    /**
     * Show the form for creating a new item-item link.
     */
    public function create(Item $item): View
    {
        $items = Item::where('id', '!=', $item->id)->orderBy('internal_name')->get();
        $contexts = Context::orderBy('internal_name')->get();
        $defaultContext = Context::where('is_default', true)->first();

        return view('item-links.create', compact('item', 'items', 'contexts', 'defaultContext'));
    }

    /**
     * Store a newly created item-item link in storage.
     */
    public function store(Item $item, StoreItemItemLinkRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['source_id'] = $item->id;

        $link = ItemItemLink::create($validated);

        // Return to item show page if coming from sidebar, otherwise show link detail
        $returnTo = $request->query('return_to', 'detail');

        if ($returnTo === 'item') {
            return redirect()
                ->route('items.show', $item)
                ->with('success', 'Item link created successfully');
        }

        return redirect()
            ->route('item-links.show', ['item' => $item, 'itemItemLink' => $link])
            ->with('success', 'Item link created successfully');
    }

    /**
     * Display the specified item-item link.
     */
    public function show(Item $item, ItemItemLink $itemItemLink): View
    {
        // Verify the link belongs to this item
        if ($itemItemLink->source_id !== $item->id) {
            abort(404);
        }

        $itemItemLink->load(['source', 'target', 'context']);

        return view('item-links.show', compact('item', 'itemItemLink'));
    }

    /**
     * Show the form for editing the specified item-item link.
     */
    public function edit(Item $item, ItemItemLink $itemItemLink): View
    {
        // Verify the link belongs to this item
        if ($itemItemLink->source_id !== $item->id) {
            abort(404);
        }

        $itemItemLink->load(['target', 'context']);

        $items = Item::where('id', '!=', $item->id)->orderBy('internal_name')->get();
        $contexts = Context::orderBy('internal_name')->get();

        return view('item-links.edit', compact('item', 'itemItemLink', 'items', 'contexts'));
    }

    /**
     * Update the specified item-item link in storage.
     */
    public function update(Item $item, ItemItemLink $itemItemLink, UpdateItemItemLinkRequest $request): RedirectResponse
    {
        // Verify the link belongs to this item
        if ($itemItemLink->source_id !== $item->id) {
            abort(404);
        }

        $itemItemLink->update($request->validated());

        return redirect()
            ->route('item-links.show', ['item' => $item, 'itemItemLink' => $itemItemLink])
            ->with('success', 'Item link updated successfully');
    }

    /**
     * Remove the specified item-item link from storage.
     */
    public function destroy(Item $item, ItemItemLink $itemItemLink): RedirectResponse
    {
        // Verify the link belongs to this item
        if ($itemItemLink->source_id !== $item->id) {
            abort(404);
        }

        $itemItemLink->delete();

        return redirect()
            ->route('item-links.index', $item)
            ->with('success', 'Item link deleted successfully');
    }
}
