<?php

namespace App\Livewire;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use InvalidArgumentException;
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

    public $scopes = null; // Optional: named Eloquent scope(s) applied to dynamic queries

    public int $perPage = 50; // Maximum options returned by a dynamic query

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
        $filterValue = null,
        $scopes = null,
        ?int $perPage = null
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
        $this->perPage = $perPage ?? (int) config('interface.searchable_select.per_page', 50);

        if ($scopes !== null) {
            $this->scopes = $this->normalizeScopes($scopes, $this->modelClass);
        }

        if ($this->staticOptions !== null) {
            $count = count(collect($this->staticOptions));
            $max = (int) config('interface.searchable_select.static_options_max', 50);
            if ($count > $max) {
                throw new InvalidArgumentException(
                    "SearchableSelect received {$count} staticOptions but the configured maximum is {$max}. Use dynamic mode (modelClass + scope) for growable entities."
                );
            }
        }
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

            // Apply named scopes
            if ($this->scopes) {
                foreach ($this->scopes as $scopeEntry) {
                    $query->{$scopeEntry['scope']}(...$scopeEntry['args']);
                }
            }

            // Prefix-first search: prefix matches are ordered before infix matches
            $search = trim($this->search);
            if ($search !== '') {
                $grammar = $query->getModel()->getConnection()->getQueryGrammar();
                $wrappedColumn = $grammar->wrap($this->displayField);
                $query->where($this->displayField, 'LIKE', "%{$search}%")
                    ->orderByRaw(
                        "CASE WHEN {$wrappedColumn} LIKE ? THEN 0 ELSE 1 END",
                        ["{$search}%"]
                    )
                    ->orderBy($this->displayField);
            } else {
                $query->orderBy($this->displayField);
            }

            return $query->limit($this->perPage)->get();
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

    /**
     * Normalise the $scopes mount parameter into the canonical
     * array<int, array{scope: string, args: array}> shape.
     *
     * Accepted input shapes:
     *   string                                    → single scope, no args
     *   array<int, string>                        → multiple scopes, no args
     *   array<int, array{scope: string, args: array}> → fully specified
     *
     * @throws InvalidArgumentException for non-alphanumeric names, unknown scopes, or non-serializable args
     */
    private function normalizeScopes(mixed $scopes, ?string $modelClass): array
    {
        if (is_string($scopes)) {
            $scopes = [$scopes];
        }

        if (! is_array($scopes)) {
            throw new InvalidArgumentException('The scopes parameter must be a string or an array.');
        }

        $normalized = [];

        foreach ($scopes as $scope) {
            if (is_string($scope)) {
                $this->validateScopeName($scope, $modelClass);
                $normalized[] = ['scope' => $scope, 'args' => []];
            } elseif (is_array($scope) && array_key_exists('scope', $scope)) {
                $this->validateScopeName($scope['scope'], $modelClass);
                $args = $scope['args'] ?? [];
                $this->validateScopeArgs($args);
                $normalized[] = ['scope' => $scope['scope'], 'args' => array_values((array) $args)];
            } else {
                throw new InvalidArgumentException('Each scope must be a string or an array with a "scope" key.');
            }
        }

        return $normalized;
    }

    private function validateScopeName(string $name, ?string $modelClass): void
    {
        if (! preg_match('/^[a-zA-Z0-9]+$/', $name)) {
            throw new InvalidArgumentException(
                "Scope name '{$name}' contains non-alphanumeric characters. Only alphanumeric scope names are allowed."
            );
        }

        if ($modelClass !== null && ! method_exists($modelClass, 'scope'.Str::studly($name))) {
            throw new InvalidArgumentException(
                "Scope '{$name}' does not exist on model {$modelClass}."
            );
        }
    }

    private function validateScopeArgs(mixed $args): void
    {
        foreach ((array) $args as $arg) {
            if (! is_scalar($arg) && ! is_null($arg) && ! is_array($arg)) {
                throw new InvalidArgumentException(
                    'Scope arguments must be scalars, arrays, or null. Objects (including Eloquent models) are not allowed in component state.'
                );
            }
        }
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
