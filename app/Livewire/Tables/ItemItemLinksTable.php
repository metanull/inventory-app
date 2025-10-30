<?php

namespace App\Livewire\Tables;

use App\Models\Context;
use App\Models\Item;
use App\Models\ItemItemLink;
use Livewire\Component;
use Livewire\WithPagination;

class ItemItemLinksTable extends Component
{
    use WithPagination;

    public ?string $item_id = null;

    public string $q = '';

    public int $perPage = 0;

    public string $sortBy = 'created_at';

    public string $sortDirection = 'desc';

    public string $contextFilter = '';

    protected $queryString = [
        'q' => ['except' => ''],
        'perPage' => ['except' => 10],
        'sortBy' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
        'contextFilter' => ['except' => ''],
    ];

    public function mount(?Item $item = null): void
    {
        $this->item_id = $item?->id;
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

    public function updatingContextFilter(): void
    {
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

    public function sortBy(string $field): void
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    public function getItemItemLinksProperty()
    {
        $query = ItemItemLink::with(['source', 'target', 'context']);

        if ($this->item_id) {
            $query->where('source_id', $this->item_id);
        }

        $search = trim($this->q);
        if ($search !== '') {
            $query->whereHas('target', function ($q) use ($search) {
                $q->where('internal_name', 'like', "%{$search}%")
                    ->orWhere('id', 'like', "%{$search}%");
            });
        }

        if ($this->contextFilter !== '') {
            $query->where('context_id', $this->contextFilter);
        }

        $query->orderBy($this->sortBy, $this->sortDirection);

        return $query->paginate($this->perPage);
    }

    public function deleteLink(ItemItemLink $link): void
    {
        $link->delete();
    }

    public function render()
    {
        return view('livewire.tables.item-item-links-table', [
            'links' => $this->getItemItemLinksProperty(),
            'contexts' => Context::orderBy('internal_name')->get(),
        ]);
    }
}
