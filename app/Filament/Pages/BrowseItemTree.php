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
     * Maximum depth for ancestor chain traversal to prevent infinite loops.
     */
    private const MAX_ANCESTOR_DEPTH = 10;

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
     * Maximum number of root items to render at once.
     */
    private const MAX_ROOT_ITEMS = 250;

    /**
     * Fetch the root-level items (no parent), limited to avoid OOM on large datasets.
     *
     * @return Collection<int, Item>
     */
    public function getRoots(): Collection
    {
        return Item::query()
            ->whereNull('parent_id')
            ->withCount('children')
            ->orderBy('internal_name')
            ->limit(self::MAX_ROOT_ITEMS)
            ->get();
    }

    /**
     * Total count of root-level items (without loading models).
     */
    public function getRootCount(): int
    {
        return Item::query()
            ->whereNull('parent_id')
            ->count();
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
