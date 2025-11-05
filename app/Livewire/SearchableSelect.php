<?php

namespace App\Livewire;

use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Modelable;
use Livewire\Component;

/**
 * Server-side searchable select component for both static and dynamic datasets
 * Handles both static options arrays and dynamic DB queries with Livewire
 */
class SearchableSelect extends Component
{
    #[Modelable]
    public $selectedId = '';

    public string $search = '';

    public bool $open = false;

    // Configuration props
    public string $name = '';

    public $staticOptions = null; // For static options (e.g., type dropdown with 2 items)

    public ?string $modelClass = null; // For dynamic DB queries (e.g., items with 1000+ records)

    public string $displayField = 'internal_name';

    public string $valueField = 'id'; // Field to use as value (for static options)

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
        $staticOptions = null,
        ?string $modelClass = null,
        string $displayField = 'internal_name',
        string $valueField = 'id',
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
        $this->staticOptions = $staticOptions;
        $this->modelClass = $modelClass;
        $this->displayField = $displayField;
        $this->valueField = $valueField;
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

    public function getOptionsProperty()
    {
        // Static options mode: filter provided options
        if ($this->staticOptions !== null) {
            $options = collect($this->staticOptions);

            $search = trim($this->search);
            if ($search !== '') {
                $options = $options->filter(function ($option) use ($search) {
                    $displayValue = is_object($option)
                        ? ($option->{$this->displayField} ?? '')
                        : ($option[$this->displayField] ?? '');

                    return stripos($displayValue, $search) !== false;
                });
            }

            return $options;
        }

        // Dynamic DB query mode
        if ($this->modelClass) {
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

        return collect();
    }

    public function getSelectedOptionProperty()
    {
        if (! $this->selectedId) {
            return null;
        }

        // Static options mode
        if ($this->staticOptions !== null) {
            $options = collect($this->staticOptions);

            return $options->first(function ($option) {
                $value = is_object($option)
                    ? ($option->{$this->valueField} ?? null)
                    : ($option[$this->valueField] ?? null);

                return $value == $this->selectedId;
            });
        }

        // Dynamic DB query mode
        if ($this->modelClass) {
            return $this->modelClass::find($this->selectedId);
        }

        return null;
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
