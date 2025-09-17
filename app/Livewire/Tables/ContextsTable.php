<?php

namespace App\Livewire\Tables;

use App\Models\Context;
use Livewire\Component;
use Livewire\WithPagination;

class ContextsTable extends Component
{
    use WithPagination;

    public string $q = '';

    public int $perPage = 0;

    protected $queryString = [
        'q' => ['except' => ''],
        'perPage' => ['except' => 20],
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

    public function getContextsProperty()
    {
        $query = Context::query();
        $search = trim($this->q);
        if ($search !== '') {
            $query->where('internal_name', 'LIKE', "%{$search}%");
        }

        return $query->orderByDesc('created_at')->paginate($this->perPage)->withQueryString();
    }

    public function render()
    {
        $c = config('app_entities.contexts.colors', []);

        return view('livewire.tables.contexts-table', [
            'contexts' => $this->contexts,
            'c' => $c,
        ]);
    }
}
