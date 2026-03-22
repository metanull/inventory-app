<?php

namespace App\Livewire\Tables;

use App\Models\Item;
use App\Models\Tag;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Livewire dynamic table for Items with debounced search and pagination.
 */
class ItemsTable extends Component
{
    use WithPagination;

    public string $q = '';

    public int $perPage = 0;

    public string $sortBy = 'created_at';

    public string $sortDirection = 'desc';

    public array $selectedTags = [];

    public string $typeFilter = '';

    public string $parentId = '';

    public bool $hierarchyMode = true;

    protected $queryString = [
        'q' => ['except' => ''],
        // Keep in sync with config('interface.pagination.default_per_page')
        'perPage' => ['except' => 10],
        'sortBy' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
        'selectedTags' => ['except' => []],
        'typeFilter' => ['except' => ''],
        'parentId' => ['except' => ''],
        'hierarchyMode' => ['except' => true],
    ];

    public function mount(): void
    {
        $this->perPage = (int) request()->query('perPage', (int) config('interface.pagination.default_per_page'));
        $this->normalizePerPage();
    }

    public function updatingQ(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->normalizePerPage();
    }

    public function updatingSelectedTags(): void
    {
        $this->resetPage();
    }

    public function updatingTypeFilter(): void
    {
        $this->resetPage();
    }

    public function updatingParentId(): void
    {
        $this->resetPage();
    }

    public function removeTag(string $tagId): void
    {
        $this->selectedTags = array_values(array_filter($this->selectedTags, fn ($id) => $id !== $tagId));
        $this->resetPage();
    }

    public function clearTags(): void
    {
        $this->selectedTags = [];
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        $validFields = ['internal_name', 'created_at'];

        if (! in_array($field, $validFields)) {
            return;
        }

        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    public function navigateToParent(string $id): void
    {
        $this->parentId = $id;
        $this->resetPage();
    }

    public function navigateUp(): void
    {
        if ($this->parentId === '') {
            return;
        }
        $parent = Item::find($this->parentId);
        $this->parentId = $parent?->parent_id ?? '';
        $this->resetPage();
    }

    public function toggleHierarchyMode(): void
    {
        $this->hierarchyMode = ! $this->hierarchyMode;
        $this->parentId = '';
        $this->resetPage();
    }

    protected function normalizePerPage(): void
    {
        $options = array_map('intval', (array) config('interface.pagination.per_page_options'));
        $default = (int) config('interface.pagination.default_per_page');
        $max = (int) config('interface.pagination.max_per_page');

        if (! in_array((int) $this->perPage, $options, true)) {
            $this->perPage = $default;
        }

        if ($this->perPage < 1) {
            $this->perPage = $default;
        }
        if ($this->perPage > $max) {
            $this->perPage = $max;
        }
    }

    public function getBreadcrumbsProperty(): array
    {
        if ($this->parentId === '') {
            return [];
        }

        $breadcrumbs = [];
        $current = Item::find($this->parentId);
        while ($current) {
            array_unshift($breadcrumbs, $current);
            $current = $current->parent;
        }

        return $breadcrumbs;
    }

    public function getItemsProperty()
    {
        $query = Item::query()->withCount('children');
        $search = trim($this->q);
        if ($search !== '') {
            $query->where('internal_name', 'LIKE', "%{$search}%");
        }

        // Filter by tags if selected (items must have ALL selected tags)
        if (! empty($this->selectedTags)) {
            foreach ($this->selectedTags as $tagId) {
                $query->whereHas('tags', function ($q) use ($tagId) {
                    $q->where('tags.id', $tagId);
                });
            }
        }

        // Filter by type if selected
        if ($this->typeFilter !== '') {
            match ($this->typeFilter) {
                'object' => $query->objects(),
                'monument' => $query->monuments(),
                'detail' => $query->details(),
                'picture' => $query->pictures(),
                default => null,
            };
        }

        if ($this->hierarchyMode) {
            if ($this->parentId !== '') {
                $query->where('parent_id', $this->parentId);
            } else {
                $query->parents();
            }
        }

        return $query->orderBy($this->sortBy, $this->sortDirection)->paginate($this->perPage)->withQueryString();
    }

    public function getAvailableTagsProperty()
    {
        return Tag::orderBy('internal_name')->get();
    }

    public function render()
    {
        $c = config('app_entities.items.colors', []);

        return view('livewire.tables.items-table', [
            'items' => $this->items,
            'breadcrumbs' => $this->breadcrumbs,
            'availableTags' => $this->availableTags,
            'c' => $c,
        ]);
    }
}
