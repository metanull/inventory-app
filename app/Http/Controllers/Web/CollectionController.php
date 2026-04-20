<?php

namespace App\Http\Controllers\Web;

use App\Enums\Permission;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\StoreCollectionRequest;
use App\Http\Requests\Web\UpdateCollectionRequest;
use App\Models\Collection;
use App\Models\Context;
use App\Models\Item;
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
        $this->middleware('permission:'.Permission::VIEW_DATA->value)->only(['index', 'show', 'showItem']);
        $this->middleware('permission:'.Permission::CREATE_DATA->value)->only(['create', 'store']);
        $this->middleware('permission:'.Permission::UPDATE_DATA->value)->only(['edit', 'update', 'moveUp', 'moveDown', 'setParent', 'removeParent']);
        $this->middleware('permission:'.Permission::DELETE_DATA->value)->only(['destroy']);
    }

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $sort = (string) $request->query('sort', 'created_at');
        $dir = strtolower((string) $request->query('dir', 'desc'));
        $parentId = (string) $request->query('parent_id', '');
        $hierarchyMode = filter_var($request->query('hierarchy', '1'), FILTER_VALIDATE_BOOLEAN);

        $allowedSortFields = ['internal_name', 'display_order', 'created_at'];
        if (! in_array($sort, $allowedSortFields, true)) {
            $sort = 'created_at';
        }
        if (! in_array($dir, ['asc', 'desc'], true)) {
            $dir = 'desc';
        }

        $query = Collection::query()->with(['context', 'language'])->withCount('children');

        if ($search !== '') {
            $query->where('internal_name', 'LIKE', "%{$search}%");
        }

        if ($hierarchyMode) {
            if ($parentId !== '') {
                $query->childrenOf($parentId);
            } else {
                $query->roots();
            }
        }

        $query->orderBy($sort, $dir);
        $perPage = $this->resolvePerPage($request);
        $collections = $query->paginate($perPage)->withQueryString();

        $breadcrumbs = $this->buildIndexBreadcrumbs($parentId);

        return view('collections.index', compact(
            'collections', 'search', 'sort', 'dir', 'parentId', 'hierarchyMode', 'breadcrumbs',
        ));
    }

    public function show(Collection $collection): View
    {
        $collection->load([
            'context',
            'language',
            'parent',
            'children',
            'translations.context',
            'translations.language',
            'attachedItems.itemImages',
        ]);

        $breadcrumbs = $this->buildAncestorBreadcrumbs($collection);

        return view('collections.show', compact('collection', 'breadcrumbs'));
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

    public function showItem(Collection $collection, Item $item): View
    {
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

    private function buildIndexBreadcrumbs(string $parentId): array
    {
        if ($parentId === '') {
            return [];
        }

        $breadcrumbs = [];
        $current = Collection::with('parent.parent.parent.parent')->find($parentId);
        while ($current) {
            array_unshift($breadcrumbs, $current);
            $current = $current->parent;
        }

        return $breadcrumbs;
    }
}
