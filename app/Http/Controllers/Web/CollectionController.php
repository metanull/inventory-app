<?php

namespace App\Http\Controllers\Web;

use App\Enums\Permission;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\IndexCollectionRequest;
use App\Http\Requests\Web\StoreCollectionRequest;
use App\Http\Requests\Web\UpdateCollectionRequest;
use App\Models\Collection;
use App\Models\Context;
use App\Models\Item;
use App\Models\Language;
use App\Services\Web\CollectionIndexQuery;
use App\Services\Web\CollectionShowPageData;
use App\Services\Web\ItemShowPageData;
use App\Support\Web\Lists\CollectionListDefinition;
use App\Support\Web\Lists\ListState;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CollectionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:'.Permission::VIEW_DATA->value)->only(['index', 'show', 'showItem']);
        $this->middleware('permission:'.Permission::CREATE_DATA->value)->only(['create', 'store']);
        $this->middleware('permission:'.Permission::UPDATE_DATA->value)->only(['edit', 'update', 'moveUp', 'moveDown', 'setParent', 'removeParent']);
        $this->middleware('permission:'.Permission::DELETE_DATA->value)->only(['destroy']);
    }

    public function index(IndexCollectionRequest $request, CollectionIndexQuery $collectionIndexQuery): View
    {
        $listState = $request->listState();
        $hierarchyMode = ($listState->filters['mode'] ?? CollectionListDefinition::MODE_HIERARCHY) === CollectionListDefinition::MODE_HIERARCHY;
        $parentCollection = $hierarchyMode ? $this->resolveParentCollection($listState) : null;

        return view('collections.index', [
            'collections' => $collectionIndexQuery->paginate($listState),
            'listState' => $listState,
            'hierarchyMode' => $hierarchyMode,
            'parentCollection' => $parentCollection,
            'breadcrumbs' => $hierarchyMode && $parentCollection ? $this->buildIndexBreadcrumbs($parentCollection) : [],
        ]);
    }

    public function show(Collection $collection, CollectionShowPageData $collectionShowPageData): View
    {
        $pageData = $collectionShowPageData->build($collection);
        $breadcrumbs = $this->buildAncestorBreadcrumbs($collection);

        return view('collections.show', array_merge($pageData, compact('collection', 'breadcrumbs')));
    }

    public function create(Request $request): View
    {
        $contexts = Context::query()->orderBy('internal_name')->get(['id', 'internal_name']);
        $languages = Language::query()->orderBy('id')->get(['id', 'internal_name']);
        $parentId = $request->query('parent_id');

        return view('collections.create', compact('contexts', 'languages', 'parentId'));
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

    public function showItem(Collection $collection, Item $item, ItemShowPageData $itemShowPageData): View
    {
        $pageData = $itemShowPageData->build($item);
        $breadcrumbs = $this->buildAncestorBreadcrumbs($collection);
        $breadcrumbs[] = [
            'label' => $collection->internal_name,
            'url' => route('collections.show', $collection),
        ];

        $itemAncestor = $item->parent;
        $itemCrumbs = [];
        while ($itemAncestor) {
            array_unshift($itemCrumbs, [
                'label' => $itemAncestor->internal_name,
                'url' => route('collections.items.show', [$collection, $itemAncestor]),
            ]);
            $itemAncestor = $itemAncestor->parent;
        }
        $breadcrumbs = array_merge($breadcrumbs, $itemCrumbs);

        return view('items.show', [
            'item' => $item,
            'collection' => $collection,
            'breadcrumbs' => $breadcrumbs,
            ...$pageData,
        ]);
    }

    public function attachItem(Request $request, Collection $collection): RedirectResponse
    {
        $request->validate([
            'item_id' => ['required', 'exists:items,id'],
        ]);

        $item = Item::findOrFail($request->item_id);
        $collection->attachItem($item);

        return redirect()->route('collections.show', $collection)
            ->with('success', 'Item attached successfully');
    }

    public function detachItem(Collection $collection, Item $item): RedirectResponse
    {
        $collection->detachItem($item);

        return redirect()->route('collections.show', $collection)
            ->with('success', 'Item detached successfully');
    }

    public function setParent(Request $request, Collection $collection): RedirectResponse
    {
        $request->validate([
            'parent_id' => ['required', 'exists:collections,id'],
        ]);

        if ($request->parent_id === $collection->id) {
            return redirect()->back()
                ->withErrors(['parent_id' => 'A collection cannot be its own parent'])
                ->withInput();
        }

        $potentialParent = Collection::findOrFail($request->parent_id);
        $ancestor = $potentialParent;
        while ($ancestor->parent_id !== null) {
            if ($ancestor->parent_id === $collection->id) {
                return redirect()->back()
                    ->withErrors(['parent_id' => 'Cannot create circular parent relationship'])
                    ->withInput();
            }
            $ancestor = Collection::find($ancestor->parent_id);
            if (! $ancestor) {
                break;
            }
        }

        $collection->update(['parent_id' => $request->parent_id]);

        return redirect()->route('collections.show', $collection)
            ->with('success', 'Parent collection set successfully');
    }

    public function removeParent(Collection $collection): RedirectResponse
    {
        $collection->update(['parent_id' => null]);

        return redirect()->route('collections.show', $collection)
            ->with('success', 'Parent relationship removed successfully');
    }

    public function moveUp(Collection $collection): RedirectResponse
    {
        $collection->moveUp();

        return $this->redirectAfterMove($collection, 'Collection moved up');
    }

    public function moveDown(Collection $collection): RedirectResponse
    {
        $collection->moveDown();

        return $this->redirectAfterMove($collection, 'Collection moved down');
    }

    private function redirectAfterMove(Collection $collection, string $message): RedirectResponse
    {
        $redirect = $collection->parent_id
            ? redirect()->route('collections.show', $collection->parent_id)
            : redirect()->route('collections.index');

        return $redirect->with('success', $message);
    }

    private function buildAncestorBreadcrumbs(Collection $collection): array
    {
        $breadcrumbs = [];
        $ancestor = $collection->parent;
        while ($ancestor) {
            array_unshift($breadcrumbs, [
                'label' => $ancestor->internal_name,
                'url' => route('collections.show', $ancestor),
            ]);
            $ancestor = $ancestor->parent;
        }

        return $breadcrumbs;
    }

    private function resolveParentCollection(ListState $listState): ?Collection
    {
        $parentId = $listState->filters['parent_id'] ?? null;

        if (! is_string($parentId) || $parentId === '') {
            return null;
        }

        return Collection::query()
            ->select('id', 'parent_id', 'internal_name')
            ->find($parentId);
    }

    /**
     * @return array<int, array{id: string, label: string}>
     */
    private function buildIndexBreadcrumbs(Collection $collection): array
    {
        $breadcrumbs = [];
        $ancestor = $collection;

        while ($ancestor) {
            array_unshift($breadcrumbs, [
                'id' => $ancestor->id,
                'label' => $ancestor->internal_name,
            ]);

            $ancestor = $ancestor->parent()
                ->select('id', 'parent_id', 'internal_name')
                ->first();
        }

        return $breadcrumbs;
    }
}
