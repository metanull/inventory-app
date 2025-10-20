<?php

namespace App\Http\Controllers\Web;

use App\Enums\Permission;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\StoreItemTranslationRequest;
use App\Http\Requests\Web\UpdateItemTranslationRequest;
use App\Models\Context;
use App\Models\Item;
use App\Models\ItemTranslation;
use App\Models\Language;
use App\Support\Web\SearchAndPaginate;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ItemTranslationController extends Controller
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
     * Display a listing of item translations.
     */
    public function index(Request $request): View
    {
        $query = ItemTranslation::with(['item', 'language', 'context']);

        // Apply search if provided
        $search = $request->input('q');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('alternate_name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('item', function ($itemQuery) use ($search) {
                        $itemQuery->where('internal_name', 'like', "%{$search}%")
                            ->orWhere('id', 'like', "%{$search}%");
                    });
            });
        }

        $perPage = $request->input('perPage', 15);
        $itemTranslations = $query->orderByDesc('created_at')->paginate($perPage)->withQueryString();

        return view('item-translations.index', compact('itemTranslations', 'search'));
    }

    /**
     * Show the form for creating a new item translation.
     */
    public function create(Request $request): View
    {
        $items = Item::orderBy('internal_name')->get();
        $languages = Language::orderBy('internal_name')->get();
        $contexts = Context::orderBy('internal_name')->get();
        $defaultContext = Context::where('is_default', true)->first();

        // Get item_id from query parameter if provided (from item show page)
        $selectedItemId = $request->input('item_id');

        return view('item-translations.create', compact('items', 'languages', 'contexts', 'defaultContext', 'selectedItemId'));
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

        $items = Item::orderBy('internal_name')->get();
        $languages = Language::orderBy('internal_name')->get();
        $contexts = Context::orderBy('internal_name')->get();

        return view('item-translations.edit', compact('itemTranslation', 'items', 'languages', 'contexts'));
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
}
