<?php

namespace App\Http\Controllers\Web;

use App\Enums\Permission;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\IndexItemRequest;
use App\Http\Requests\Web\StoreItemRequest;
use App\Http\Requests\Web\UpdateItemRequest;
use App\Models\Collection;
use App\Models\Country;
use App\Models\Item;
use App\Models\Partner;
use App\Models\Project;
use App\Models\Tag;
use App\Services\Web\ItemIndexQuery;
use App\Services\Web\ItemShowPageData;
use App\Support\Web\Lists\ListState;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:'.Permission::VIEW_DATA->value)->only(['index', 'show']);
        $this->middleware('permission:'.Permission::CREATE_DATA->value)->only(['create', 'store']);
        $this->middleware('permission:'.Permission::UPDATE_DATA->value)->only(['edit', 'update', 'attachTag', 'detachTag']);
        $this->middleware('permission:'.Permission::DELETE_DATA->value)->only(['destroy']);
    }

    public function index(IndexItemRequest $request, ItemIndexQuery $itemIndexQuery): View
    {
        $listState = $request->listState();
        $hierarchyMode = (bool) ($listState->filters['hierarchy'] ?? true);
        $parentItem = $this->resolveParentItem($listState);

        return view('items.index', [
            'items' => $itemIndexQuery->paginate($listState),
            'listState' => $listState,
            'hierarchyMode' => $hierarchyMode,
            'parentItem' => $parentItem,
            'breadcrumbs' => $hierarchyMode && $parentItem ? $this->buildIndexBreadcrumbs($parentItem) : [],
            'selectedTags' => $this->resolveSelectedTags($listState),
            'selectedPartner' => $this->resolveSelectedPartner($listState),
            'selectedCollection' => $this->resolveSelectedCollection($listState),
            'selectedProject' => $this->resolveSelectedProject($listState),
            'selectedCountry' => $this->resolveSelectedCountry($listState),
        ]);
    }

    public function show(Item $item, ItemShowPageData $itemShowPageData): View
    {
        $pageData = $itemShowPageData->build($item);
        $breadcrumbs = $this->buildAncestorBreadcrumbs($item);

        return view('items.show', array_merge($pageData, compact('item', 'breadcrumbs')));
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

    public function detachTag(Item $item, Tag $tag): RedirectResponse
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

    public function addChild(Request $request, Item $item): RedirectResponse
    {
        $request->validate([
            'child_id' => ['required', 'exists:items,id'],
        ]);

        // Prevent item from being its own child
        if ($request->child_id === $item->id) {
            return redirect()->back()
                ->withErrors(['child_id' => 'An item cannot be its own child'])
                ->withInput();
        }

        $child = Item::findOrFail($request->child_id);

        // Check if already a child (idempotent)
        if ($child->parent_id === $item->id) {
            return redirect()->route('items.show', $item)
                ->with('info', 'This item is already a child of the selected parent');
        }

        // Prevent circular references by checking if item is a descendant of the potential child
        $ancestor = $item;
        while ($ancestor->parent_id !== null) {
            if ($ancestor->parent_id === $child->id) {
                return redirect()->back()
                    ->withErrors(['child_id' => 'Cannot create circular child relationship'])
                    ->withInput();
            }
            $ancestor = Item::find($ancestor->parent_id);
            if (! $ancestor) {
                break;
            }
        }

        $child->update(['parent_id' => $item->id]);

        return redirect()->route('items.show', $item)
            ->with('success', 'Child item added successfully');
    }

    public function removeChild(Item $item, Item $child): RedirectResponse
    {
        // Verify the child relationship exists
        if ($child->parent_id !== $item->id) {
            return redirect()->route('items.show', $item)
                ->withErrors('This item is not a child of the selected parent');
        }

        $child->update(['parent_id' => null]);

        return redirect()->route('items.show', $item)
            ->with('success', 'Child relationship removed successfully');
    }

    private function buildAncestorBreadcrumbs(Item $item): array
    {
        $breadcrumbs = [];
        $ancestor = $item->parent;
        while ($ancestor) {
            array_unshift($breadcrumbs, [
                'label' => $ancestor->internal_name,
                'url' => route('items.show', $ancestor),
            ]);
            $ancestor = $ancestor->parent;
        }

        return $breadcrumbs;
    }

    private function resolveParentItem(ListState $listState): ?Item
    {
        $parentId = $listState->filters['parent_id'] ?? null;

        if (! is_string($parentId) || $parentId === '') {
            return null;
        }

        return Item::query()
            ->select('id', 'parent_id', 'internal_name')
            ->find($parentId);
    }

    /**
     * @return array<int, array{id: string, label: string}>
     */
    private function buildIndexBreadcrumbs(Item $item): array
    {
        $breadcrumbs = [];
        $ancestor = $item;

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

    private function resolveSelectedPartner(ListState $listState): ?Partner
    {
        $partnerId = $listState->filters['partner_id'] ?? null;

        if (! is_string($partnerId) || $partnerId === '') {
            return null;
        }

        return Partner::query()->select('id', 'internal_name')->find($partnerId);
    }

    private function resolveSelectedCollection(ListState $listState): ?Collection
    {
        $collectionId = $listState->filters['collection_id'] ?? null;

        if (! is_string($collectionId) || $collectionId === '') {
            return null;
        }

        return Collection::query()->select('id', 'internal_name')->find($collectionId);
    }

    private function resolveSelectedProject(ListState $listState): ?Project
    {
        $projectId = $listState->filters['project_id'] ?? null;

        if (! is_string($projectId) || $projectId === '') {
            return null;
        }

        return Project::query()->select('id', 'internal_name')->find($projectId);
    }

    private function resolveSelectedCountry(ListState $listState): ?Country
    {
        $countryId = $listState->filters['country_id'] ?? null;

        if (! is_string($countryId) || $countryId === '') {
            return null;
        }

        return Country::query()->select('id', 'internal_name')->find($countryId);
    }

    private function resolveSelectedTags(ListState $listState): EloquentCollection
    {
        $selectedTagIds = $listState->filters['tags'] ?? [];

        if (! is_array($selectedTagIds) || $selectedTagIds === []) {
            return new EloquentCollection;
        }

        return Tag::query()
            ->select('id', 'internal_name', 'description', 'category')
            ->whereIn('id', $selectedTagIds)
            ->orderBy('internal_name')
            ->get();
    }
}
