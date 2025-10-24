<?php

namespace App\Livewire\Tables;

use App\Models\PartnerTranslation;
use Livewire\Component;
use Livewire\WithPagination;

class PartnerTranslationsTable extends Component
{
    use WithPagination;

    public string $q = '';

    public int $perPage = 0;

    public string $sortBy = 'created_at';

    public string $sortDirection = 'desc';

    public string $contextFilter = '';

    public string $languageFilter = '';

    protected $queryString = [
        'q' => ['except' => ''],
        'perPage' => ['except' => 10],
        'sortBy' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
        'contextFilter' => ['except' => ''],
        'languageFilter' => ['except' => ''],
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

    public function updatingLanguageFilter(): void
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

    public function getPartnerTranslationsProperty()
    {
        $query = PartnerTranslation::with(['partner', 'language', 'context']);
        $search = trim($this->q);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%")
                    ->orWhere('city_display', 'LIKE', "%{$search}%")
                    // Search in parent partner's internal_name and ID
                    ->orWhereHas('partner', function ($partnerQuery) use ($search) {
                        $partnerQuery->where('internal_name', 'LIKE', "%{$search}%")
                            ->orWhere('id', 'LIKE', "%{$search}%");
                    });
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

        // Filter by language if specified
        if ($this->languageFilter === 'default') {
            $defaultLanguage = \App\Models\Language::where('is_default', true)->first();
            if ($defaultLanguage) {
                $query->where('language_id', $defaultLanguage->id);
            }
        } elseif ($this->languageFilter !== '' && $this->languageFilter !== 'all') {
            $query->where('language_id', $this->languageFilter);
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
        $c = config('app_entities.partner_translations.colors', []);
        $contexts = \App\Models\Context::orderBy('internal_name')->get();
        $languages = \App\Models\Language::orderBy('internal_name')->get();

        return view('livewire.tables.partner-translations-table', [
            'partnerTranslations' => $this->partnerTranslations,
            'contexts' => $contexts,
            'languages' => $languages,
            'c' => $c,
        ]);
    }
}
