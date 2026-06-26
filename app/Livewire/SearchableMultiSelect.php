<?php

namespace App\Livewire;

use App\Livewire\Support\OptionsLookup;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\View\View;
use InvalidArgumentException;
use Livewire\Attributes\Modelable;
use Livewire\Component;

/**
 * Server-side searchable multi-select component for dynamic datasets.
 *
 * Mirrors SearchableSelect's dynamic mode but holds an array of selected ids
 * and renders a chip for each. Emits hidden name[] inputs so standard HTML
 * form submission round-trips cleanly through the existing IndexListRequest
 * filter resolution pipeline.
 *
 * Selected ids are stored as plain strings only — no Eloquent model instances
 * are held in component state, so the Livewire snapshot stays small regardless
 * of the number of selected items.
 */
class SearchableMultiSelect extends Component
{
    use OptionsLookup;

    /** @var array<int, string|int> */
    #[Modelable]
    public array $selectedIds = [];

    public string $search = '';

    public bool $open = false;

    public string $name = '';

    /** @var mixed */
    public $staticOptions = null;

    public ?string $modelClass = null;

    public string $displayField = 'internal_name';

    public string $placeholder = 'Select...';

    public string $searchPlaceholder = 'Type to search...';

    public ?string $entity = null;

    public ?string $filterColumn = null;

    public ?string $filterOperator = '!=';

    /** @var mixed */
    public $filterValue = null;

    /** @var mixed */
    public $scopes = null;

    public int $perPage = 50;

    /**
     * @param  array<int, string|int>  $selectedIds
     */
    public function mount(
        array $selectedIds = [],
        string $name = '',
        mixed $staticOptions = null,
        ?string $modelClass = null,
        string $displayField = 'internal_name',
        string $placeholder = 'Select...',
        string $searchPlaceholder = 'Type to search...',
        ?string $entity = null,
        ?string $filterColumn = null,
        ?string $filterOperator = '!=',
        mixed $filterValue = null,
        mixed $scopes = null,
        ?int $perPage = null
    ): void {
        $this->selectedIds = $selectedIds;
        $this->name = $name;
        $this->staticOptions = $staticOptions;
        $this->modelClass = $modelClass;
        $this->displayField = $displayField;
        $this->placeholder = $placeholder;
        $this->searchPlaceholder = $searchPlaceholder;
        $this->entity = $entity;
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
                    "SearchableMultiSelect received {$count} staticOptions but the configured maximum is {$max}. Use dynamic mode (modelClass + scope) for growable entities."
                );
            }
        }
    }

    public function updatedSearch(): void
    {
        $this->open = true;
    }

    /**
     * Add an option by id. Duplicate additions are no-ops.
     */
    public function addOption(string $id): void
    {
        if (! in_array($id, $this->selectedIds, true)) {
            $this->selectedIds[] = $id;
        }
        $this->search = '';
        $this->open = false;
    }

    /**
     * Remove an option by id.
     */
    public function removeOption(string $id): void
    {
        $this->selectedIds = array_values(
            array_filter($this->selectedIds, fn (string|int $existing): bool => $existing !== $id)
        );
    }

    /**
     * Clear all selected ids and reset the search field.
     */
    public function clear(): void
    {
        $this->selectedIds = [];
        $this->search = '';
    }

    /**
     * Candidate options for the dropdown (excludes already-selected ids).
     */
    /**
     * @return Collection<int, mixed>
     */
    public function getOptionsProperty()
    {
        if ($this->staticOptions !== null) {
            return $this->resolveStaticOptions()->filter(function ($option) {
                $value = is_object($option) ? ($option->id ?? null) : (is_array($option) ? ($option['id'] ?? null) : null);

                return ! in_array(is_scalar($value) ? (string) $value : '', $this->selectedIds, true);
            });
        }

        if ($this->modelClass) {
            /** @var Collection<int, mixed> $results */
            $results = $this->resolveOptionsQuery()
                ->whereNotIn('id', $this->selectedIds)
                ->get();

            return $results;
        }

        return collect();
    }

    /**
     * Currently selected options — queried by id so we never store model instances
     * in component state.
     *
     * @return Collection<int, mixed>
     */
    public function getSelectedOptionsProperty()
    {
        if (empty($this->selectedIds)) {
            return collect();
        }

        if ($this->staticOptions !== null) {
            /** @var array<int, mixed> $rawOpts */
            $rawOpts = $this->staticOptions;
            $allOptions = collect($rawOpts);

            return $allOptions->filter(function ($option) {
                $value = is_object($option) ? ($option->id ?? null) : (is_array($option) ? ($option['id'] ?? null) : null);

                return in_array(is_scalar($value) ? (string) $value : '', $this->selectedIds, true);
            });
        }

        if ($this->modelClass) {
            /** @var class-string<Model> $modelClass */
            $modelClass = $this->modelClass;

            return $modelClass::whereIn('id', $this->selectedIds)->get()->toBase();
        }

        return collect();
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

        return view('livewire.searchable-multi-select', [
            'options' => $this->getOptionsProperty(),
            'selectedOptions' => $this->getSelectedOptionsProperty(),
            'focusClasses' => $focusClasses,
        ]);
    }
}
