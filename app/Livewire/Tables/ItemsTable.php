<?php

namespace App\Livewire\Tables;

use App\Models\Item;
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

    protected $queryString = [
        'q' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    public function mount(): void
    {
        $this->perPage = (int) config('interface.pagination.default_per_page');
    }

    public function updatingQ(): void
    {
        $this->resetPage();
    }

    public function getItemsProperty()
    {
        $query = Item::query();
        $search = trim($this->q);
        if ($search !== '') {
            $query->where('internal_name', 'LIKE', "%{$search}%");
        }

        return $query->orderByDesc('created_at')->paginate($this->perPage);
    }

    public function render()
    {
        $c = config('app_entities.items.colors', []);

        return view('livewire.tables.items-table', [
            'items' => $this->items,
            'c' => $c,
        ]);
    }
}
