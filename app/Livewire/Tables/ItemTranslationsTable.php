<?php

namespace App\Livewire\Tables;

use App\Models\ItemTranslation;
use Livewire\Component;
use Livewire\WithPagination;

class ItemTranslationsTable extends Component
{
    use WithPagination;

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

    public function getItemTranslationsProperty()
    {
        $query = ItemTranslation::with(['item', 'language', 'context']);
        $search = trim($this->q);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('alternate_name', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        // Filter by context if specified
        if ($this->contextFilter === 'default') {
            $defaultContext = \App\Models\Context::where('is_default', true)->first();
            if ($defaultContext) {
                $query->where('context_id', $defaultContext->id);
            }
        } elseif ($this->contextFilter !== '' && $this->contextFilter !== 'all') {
            $query->where('context_id', $this->contextFilter);
        }

        // Apply sorting
        $validSortFields = ['name', 'created_at', 'updated_at'];
        $sortField = in_array($this->sortBy, $validSortFields) ? $this->sortBy : 'created_at';
        $sortDirection = in_array(strtolower($this->sortDirection), ['asc', 'desc']) ? $this->sortDirection : 'desc';

        $query->orderBy($sortField, $sortDirection);

        return $query->paginate($this->perPage)->withQueryString();
    }

    public function render()
    {
        $c = config('app_entities.item_translations.colors', []);
        $contexts = \App\Models\Context::orderBy('internal_name')->get();

        return view('livewire.tables.item-translations-table', [
            'itemTranslations' => $this->itemTranslations,
            'contexts' => $contexts,
            'c' => $c,
        ]);
    }
}
