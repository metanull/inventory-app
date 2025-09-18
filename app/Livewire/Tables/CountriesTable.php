<?php

namespace App\Livewire\Tables;

use App\Models\Country;
use Livewire\Component;
use Livewire\WithPagination;

class CountriesTable extends Component
{
    use WithPagination;

    public string $q = '';

    public int $perPage = 0;

    public string $sortBy = 'id';

    public string $sortDirection = 'asc';

    protected $queryString = [
        'q' => ['except' => ''],
        'perPage' => ['except' => 20],
        'sortBy' => ['except' => 'id'],
        'sortDirection' => ['except' => 'asc'],
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

    public function sortBy(string $field): void
    {
        $validFields = ['id', 'internal_name'];

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

    public function getCountriesProperty()
    {
        $query = Country::query();
        $search = trim($this->q);
        if ($search !== '') {
            $query->where('internal_name', 'LIKE', "%{$search}%");
        }

        return $query->orderBy($this->sortBy, $this->sortDirection)->paginate($this->perPage)->withQueryString();
    }

    public function render()
    {
        return view('livewire.tables.countries-table', [
            'countries' => $this->countries,
        ]);
    }
}
