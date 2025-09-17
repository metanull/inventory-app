<?php

namespace App\Livewire\Tables;

use App\Models\Language;
use Livewire\Component;
use Livewire\WithPagination;

class LanguagesTable extends Component
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

    public function getLanguagesProperty()
    {
        $query = Language::query();
        $search = trim($this->q);
        if ($search !== '') {
            $query->where('internal_name', 'LIKE', "%{$search}%");
        }

        return $query->orderBy('id')->paginate($this->perPage)->withQueryString();
    }

    public function render()
    {
        return view('livewire.tables.languages-table', [
            'languages' => $this->languages,
        ]);
    }
}
