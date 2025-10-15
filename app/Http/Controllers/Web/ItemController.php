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
        $this->middleware('permission:'.Permission::UPDATE_DATA->value)->only(['edit', 'update']);
        $this->middleware('permission:'.Permission::DELETE_DATA->value)->only(['destroy']);
    }

    public function index(Request $request): View
    {
        [$items, $search] = $this->searchAndPaginate(Item::query(), $request);

        return view('items.index', compact('items', 'search'));
    }

    public function show(Item $item): View
    {
        return view('items.show', compact('item'));
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
}
