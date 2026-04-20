<?php

namespace App\Livewire\Tables;

use App\Models\Collection;
use Livewire\Component;
use Livewire\WithPagination;

class CollectionsTable extends Component
{
    use WithPagination;

    public string $q = '';

    public int $perPage = 0;

    public string $sortBy = 'created_at';

    public string $sortDirection = 'desc';

    public string $parentId = '';

    public bool $hierarchyMode = true;

    protected $queryString = [
        'q' => ['except' => ''],
        'perPage' => ['except' => 10],
        'sortBy' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
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

    public function updatingParentId(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        $validFields = ['internal_name', 'display_order', 'created_at'];

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
        $parent = Collection::find($this->parentId);
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
        $current = Collection::find($this->parentId);
        while ($current) {
            array_unshift($breadcrumbs, $current);
            $current = $current->parent;
        }

        return $breadcrumbs;
    }

    public function getCollectionsProperty()
    {
        $query = Collection::query()->with(['context', 'language'])->withCount('children');
        $search = trim($this->q);
        if ($search !== '') {
            $query->where('internal_name', 'LIKE', "%{$search}%");
        }

        if ($this->hierarchyMode) {
            if ($this->parentId !== '') {
                $query->childrenOf($this->parentId);
            } else {
                $query->roots();
            }
        }

        $validSortFields = ['internal_name', 'display_order', 'created_at', 'updated_at'];
        $sortField = in_array($this->sortBy, $validSortFields) ? $this->sortBy : 'created_at';
        $sortDirection = in_array(strtolower($this->sortDirection), ['asc', 'desc']) ? $this->sortDirection : 'desc';

        return $query->orderBy($sortField, $sortDirection)->paginate($this->perPage)->withQueryString();
    }

    public function render()
    {
        $c = config('app_entities.collections.colors', []);

        return view('livewire.tables.collections-table', [
            'collections' => $this->collections,
            'breadcrumbs' => $this->breadcrumbs,
            'c' => $c,
        ]);
    }
}
