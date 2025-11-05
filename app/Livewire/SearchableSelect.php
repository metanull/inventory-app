<?php

namespace App\Livewire;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Modelable;
use Livewire\Component;

/**
 * Server-side searchable select component for large datasets
 * Replaces Alpine.js client-side filtering with Livewire server-side search
 */
class SearchableSelect extends Component
{
    #[Modelable]
    public $selectedId = '';

    public string $search = '';

    public bool $open = false;

    // Configuration props
    public string $name = '';

    public string $modelClass = '';

    public string $displayField = 'internal_name';

    public string $placeholder = 'Select...';

    public string $searchPlaceholder = 'Type to search...';

    public ?string $entity = null;

    public bool $required = false;

    public ?string $filterColumn = null; // Optional: column to filter on (e.g., 'id')

    public ?string $filterOperator = '!='; // Optional: operator for filter (e.g., '!=', '<>', 'IN', 'NOT IN')

    public $filterValue = null; // Optional: value(s) to filter (e.g., '123' or ['123', '456'])

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function mount(
        $selectedId = '',
        string $name = '',
        string $modelClass = '',
        string $displayField = 'internal_name',
        string $placeholder = 'Select...',
        string $searchPlaceholder = 'Type to search...',
        ?string $entity = null,
        bool $required = false,
        ?string $filterColumn = null,
        ?string $filterOperator = '!=',
        $filterValue = null
    ): void {
        $this->selectedId = old($name, $selectedId);
        $this->name = $name;
        $this->modelClass = $modelClass;
        $this->displayField = $displayField;
        $this->placeholder = $placeholder;
        $this->searchPlaceholder = $searchPlaceholder;
        $this->entity = $entity;
        $this->required = $required;
        $this->filterColumn = $filterColumn;
        $this->filterOperator = $filterOperator;
        $this->filterValue = $filterValue;
    }

    public function updatedSearch(): void
    {
        $this->open = true;
    }

    public function selectOption($id): void
    {
        $this->selectedId = $id;
        $this->search = '';
        $this->open = false;
    }

    public function clear(): void
    {
        $this->selectedId = '';
        $this->search = '';
    }

    public function getOptionsProperty(): Collection
    {
        $query = $this->modelClass::query();

        // Apply filter if provided
        if ($this->filterColumn && $this->filterValue !== null) {
            $this->applyFilter($query);
        }

        // Apply search
        $search = trim($this->search);
        if ($search !== '') {
            $query->where($this->displayField, 'LIKE', "%{$search}%");
        }

        return $query->orderBy($this->displayField)->limit(50)->get();
    }

    public function getSelectedOptionProperty()
    {
        if (! $this->selectedId) {
            return null;
        }

        return $this->modelClass::find($this->selectedId);
    }

    protected function applyFilter(Builder $query): void
    {
        match (strtoupper($this->filterOperator)) {
            'IN' => $query->whereIn($this->filterColumn, (array) $this->filterValue),
            'NOT IN' => $query->whereNotIn($this->filterColumn, (array) $this->filterValue),
            default => $query->where($this->filterColumn, $this->filterOperator, $this->filterValue),
        };
    }

    public function render()
    {
        $colors = $this->entity ? config("app_entities.{$this->entity}.colors", []) : null;
        $focusClasses = $colors
            ? "focus:border-{$colors['base']} focus:ring-{$colors['base']}"
            : 'focus:border-indigo-500 focus:ring-indigo-500';

        return view('livewire.searchable-select', [
            'options' => $this->options,
            'selectedOption' => $this->selectedOption,
            'focusClasses' => $focusClasses,
        ]);
    }
}
