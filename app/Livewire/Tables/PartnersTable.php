<?php

namespace App\Livewire\Tables;

use App\Models\Partner;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Livewire dynamic table for Partners with debounced search and pagination.
 */
class PartnersTable extends Component
{
    use WithPagination;

    public string $q = '';

    public int $perPage = 0; // resolved in mount

    protected $queryString = [
        'q' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    protected $listeners = [
        'refreshPartnersTable' => '$refresh',
    ];

    public function mount(): void
    {
        $this->perPage = (int) config('interface.pagination.default_per_page');
    }

    public function updatingQ(): void
    {
        // reset to first page when search changes
        $this->resetPage();
    }

    public function getPartnersProperty()
    {
        $query = Partner::query()->with('country');
        $search = trim($this->q);
        if ($search !== '') {
            $query->where('internal_name', 'LIKE', "%{$search}%");
        }

        return $query->orderByDesc('created_at')->paginate($this->perPage);
    }

    public function render()
    {
        $c = config('app_entities.partners.colors', []);

        return view('livewire.tables.partners-table', [
            'partners' => $this->partners,
            'c' => $c,
        ]);
    }
}
