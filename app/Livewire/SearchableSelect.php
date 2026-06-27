<?php

namespace App\Livewire;

use App\Livewire\Support\OptionsLookup;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\View\View;
use InvalidArgumentException;
use Livewire\Attributes\Modelable;
use Livewire\Component;

class SearchableSelect extends Component
{
    use OptionsLookup;

    #[Modelable]
    public mixed $selectedId = '';

    public string $search = '';

    public bool $open = false;

    // Configuration props
    public string $name = '';

    /** @var mixed */
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

    /** @var mixed */
    public $filterValue = null; // Optional: value(s) to filter (e.g., '123' or ['123', '456'])

    /** @var mixed */
    public $scopes = null; // Optional: named Eloquent scope(s) applied to dynamic queries

    public int $perPage = 50; // Maximum options returned by a dynamic query

    /** @var array<string, array<string, mixed>> */
    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function mount(
        mixed $selectedId = '',
        string $name = '',
        mixed $staticOptions = null,
        ?string $modelClass = null,
        string $displayField = 'internal_name',
        string $valueField = 'id',
        string $placeholder = 'Select...',
        string $searchPlaceholder = 'Type to search...',
        ?string $entity = null,
        bool $required = false,
        ?string $filterColumn = null,
        ?string $filterOperator = '!=',
        mixed $filterValue = null,
        mixed $scopes = null,
        ?int $perPage = null
    ): void {
        $this->selectedId = old($name, is_string($selectedId) || is_array($selectedId) ? $selectedId : null);
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
        $this->perPage = $perPage ?? Config::integer('interface.searchable_select.per_page');

        if ($scopes !== null) {
            $this->scopes = $this->normalizeScopes($scopes, $this->modelClass);
        }

        if ($this->staticOptions !== null) {
            /** @var array<int, mixed> $rawOpts */
            $rawOpts = $this->staticOptions;
            $staticCollection = collect($rawOpts);
            $count = count($staticCollection);
            $max = Config::integer('interface.searchable_select.static_options_max');
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

    public function selectOption(mixed $id): void
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

    /**
     * @return Collection<int, mixed>
     */
    public function getOptionsProperty()
    {
        // Static options mode: filter provided options
        if ($this->staticOptions !== null) {
            return $this->resolveStaticOptions();
        }

        // Dynamic DB query mode
        if ($this->modelClass) {
            /** @var Collection<int, mixed> $results */
            $results = $this->resolveOptionsQuery()->get();

            return $results;
        }

        return collect();
    }

    /**
     * @return mixed
     */
    public function getSelectedOptionProperty()
    {
        if (! $this->selectedId) {
            return null;
        }

        // Static options mode
        if ($this->staticOptions !== null) {
            /** @var array<int, mixed> $rawOpts */
            $rawOpts = $this->staticOptions;
            $options = collect($rawOpts);

            return $options->first(function ($option) {
                $value = is_object($option)
                    ? ($option->{$this->valueField} ?? null)
                    : (is_array($option) ? ($option[$this->valueField] ?? null) : null);

                return $value == $this->selectedId;
            });
        }

        // Dynamic DB query mode
        if ($this->modelClass) {
            return $this->modelClass::find($this->selectedId);
        }

        return null;
    }

    public function render(): View
    {
        $colorsRaw = $this->entity ? config("app_entities.{$this->entity}.colors", []) : null;
        $colors = is_array($colorsRaw) ? $colorsRaw : null;
        $baseRaw = $colors !== null ? ($colors['base'] ?? null) : null;
        $base = is_string($baseRaw) ? $baseRaw : '';
        $focusClasses = ($base !== '')
            ? "focus:border-{$base} focus:ring-{$base}"
            : 'focus:border-indigo-500 focus:ring-indigo-500';

        return view('livewire.searchable-select', [
            'options' => $this->getOptionsProperty(),
            'selectedOption' => $this->getSelectedOptionProperty(),
            'focusClasses' => $focusClasses,
        ]);
    }
}
