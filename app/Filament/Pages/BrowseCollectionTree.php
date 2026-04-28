<?php

namespace App\Filament\Pages;

use App\Enums\Permission;
use App\Models\Collection;
use Filament\Pages\Page;

class BrowseCollectionTree extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-queue-list';

    protected static ?string $navigationLabel = 'Browse tree';

    protected static ?string $navigationGroup = 'Inventory';

    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.browse-collection-tree';

    public static function canAccess(): bool
    {
        return auth()->user()?->hasPermissionTo(Permission::VIEW_DATA->value) ?? false;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    /**
     * IDs of expanded tree nodes.
     *
     * @var array<string, true>
     */
    public array $expanded = [];

    /**
     * Root search query string.
     */
    public string $search = '';

    /**
     * Current pagination page for root collections (1-based).
     */
    public int $page = 1;

    /**
     * Number of root collections shown per page.
     */
    private const PAGE_SIZE = 50;

    /**
     * Maximum depth for ancestor chain traversal to prevent infinite loops.
     */
    private const MAX_ANCESTOR_DEPTH = 10;

    /**
     * Reset pagination when the search query changes.
     */
    public function updatedSearch(): void
    {
        $this->page = 1;
    }

    /**
     * Expand a tree node by loading its children.
     */
    public function expand(string $id): void
    {
        $this->expanded[$id] = true;
    }

    /**
     * Collapse a tree node.
     */
    public function collapse(string $id): void
    {
        unset($this->expanded[$id]);
    }

    /**
     * Toggle expand/collapse for a node.
     */
    public function toggle(string $id): void
    {
        if (isset($this->expanded[$id])) {
            $this->collapse($id);
        } else {
            $this->expand($id);
        }
    }

    /**
     * Advance to the next page of roots.
     */
    public function nextPage(): void
    {
        if ($this->page < $this->getTotalPages()) {
            $this->page++;
        }
    }

    /**
     * Return to the previous page of roots.
     */
    public function previousPage(): void
    {
        if ($this->page > 1) {
            $this->page--;
        }
    }

    /**
     * Total number of pages for the current root query.
     */
    public function getTotalPages(): int
    {
        return (int) max(1, ceil($this->getRootCount() / self::PAGE_SIZE));
    }

    /**
     * Fetch a paginated, optionally-searched page of root-level collections (no parent).
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Collection>
     */
    public function getRoots(): \Illuminate\Database\Eloquent\Collection
    {
        $query = Collection::query()
            ->whereNull('parent_id')
            ->withCount('children')
            ->withCount('items')
            ->orderBy('internal_name');

        if ($this->search !== '') {
            $term = $this->search;
            $query->where(function ($q) use ($term): void {
                $q->where('internal_name', 'like', '%'.$term.'%')
                    ->orWhere('backward_compatibility', 'like', '%'.$term.'%');
            });
        }

        return $query
            ->offset(($this->page - 1) * self::PAGE_SIZE)
            ->limit(self::PAGE_SIZE)
            ->get();
    }

    /**
     * Total count of root-level collections matching the current search (without loading models).
     */
    public function getRootCount(): int
    {
        $query = Collection::query()->whereNull('parent_id');

        if ($this->search !== '') {
            $term = $this->search;
            $query->where(function ($q) use ($term): void {
                $q->where('internal_name', 'like', '%'.$term.'%')
                    ->orWhere('backward_compatibility', 'like', '%'.$term.'%');
            });
        }

        return $query->count();
    }

    /**
     * Fetch direct children for a given parent ID.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Collection>
     */
    public function getChildren(string $parentId): \Illuminate\Database\Eloquent\Collection
    {
        return Collection::query()
            ->where('parent_id', $parentId)
            ->withCount('children')
            ->withCount('items')
            ->orderBy('internal_name')
            ->get();
    }

    /**
     * Build the ancestor breadcrumb chain for a given collection, ordered root-first.
     *
     * Uses iterative parent lookups bounded by MAX_ANCESTOR_DEPTH to prevent
     * infinite loops in corrupted hierarchies.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Collection>
     */
    public function getAncestors(string $collectionId): \Illuminate\Database\Eloquent\Collection
    {
        $ancestorIds = [];
        $currentId = $collectionId;

        for ($depth = 0; $depth < self::MAX_ANCESTOR_DEPTH; $depth++) {
            $parentId = Collection::where('id', $currentId)->value('parent_id');
            if ($parentId === null) {
                break;
            }
            $ancestorIds[] = $parentId;
            $currentId = $parentId;
        }

        if (empty($ancestorIds)) {
            return new \Illuminate\Database\Eloquent\Collection;
        }

        // Fetch all ancestors in one query, then reorder to root-first using the collected order.
        $byId = Collection::whereIn('id', $ancestorIds)
            ->get()
            ->keyBy('id');

        // ancestorIds is leaf-to-root; reverse for root-first breadcrumb order.
        return new \Illuminate\Database\Eloquent\Collection(
            array_filter(
                array_map(fn (string $id) => $byId->get($id), array_reverse($ancestorIds))
            )
        );
    }
}
