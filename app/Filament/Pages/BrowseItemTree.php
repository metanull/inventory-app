<?php

namespace App\Filament\Pages;

use App\Enums\Permission;
use App\Models\Item;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Collection;

class BrowseItemTree extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-queue-list';

    protected static ?string $navigationLabel = 'Browse item tree';

    protected static ?string $navigationGroup = 'Inventory';

    protected static ?int $navigationSort = 3;

    protected static string $view = 'filament.pages.browse-item-tree';

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
     * Current pagination page for root items (1-based).
     */
    public int $page = 1;

    /**
     * Number of root items shown per page.
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
     * Fetch a paginated, optionally-searched page of root-level items (no parent).
     *
     * @return Collection<int, Item>
     */
    public function getRoots(): Collection
    {
        $query = Item::query()
            ->whereNull('parent_id')
            ->withCount('children')
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
     * Total count of root-level items matching the current search (without loading models).
     */
    public function getRootCount(): int
    {
        $query = Item::query()->whereNull('parent_id');

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
     * @return Collection<int, Item>
     */
    public function getChildren(string $parentId): Collection
    {
        return Item::query()
            ->where('parent_id', $parentId)
            ->withCount('children')
            ->orderBy('display_order')
            ->orderBy('internal_name')
            ->get();
    }
}
