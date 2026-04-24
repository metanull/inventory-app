<?php

namespace App\Filament\Pages;

use App\Models\Collection;
use Filament\Pages\Page;

class BrowseCollectionTree extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-queue-list';

    protected static ?string $navigationLabel = 'Browse tree';

    protected static ?string $navigationGroup = 'Inventory';

    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.browse-collection-tree';

    /**
     * IDs of expanded tree nodes.
     *
     * @var array<string, true>
     */
    public array $expanded = [];

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
     * Fetch the root-level collections (no parent).
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Collection>
     */
    public function getRoots(): \Illuminate\Database\Eloquent\Collection
    {
        return Collection::query()
            ->whereNull('parent_id')
            ->withCount('children')
            ->withCount('items')
            ->orderBy('internal_name')
            ->get();
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
     * Build the ancestor breadcrumb chain for a given collection.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Collection>
     */
    public function getAncestors(string $collectionId): \Illuminate\Database\Eloquent\Collection
    {
        $ancestorIds = [];
        $currentId = $collectionId;
        $maxDepth = 10;

        for ($depth = 0; $depth < $maxDepth; $depth++) {
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

        return Collection::whereIn('id', $ancestorIds)
            ->orderByRaw('FIELD(id, '.implode(',', array_map(fn ($id) => "'".addslashes($id)."'", array_reverse($ancestorIds))).')')
            ->get();
    }
}
