<?php

namespace App\Livewire\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * Shared query-composition trait for Livewire option-picker components.
 *
 * Consumed by SearchableSelect and SearchableMultiSelect to ensure both
 * components query candidates identically (prefix-first search, scope
 * composition, ceiling guard, filter application).
 *
 * The trait is pure query composition — it does not use any Livewire
 * lifecycle method, and must not introduce constructor-bound dependencies.
 * It reads configuration and state from the consuming component's public
 * properties ($modelClass, $displayField, $perPage, $scopes, $search,
 * $staticOptions, $filterColumn, $filterOperator, $filterValue).
 */
trait OptionsLookup
{
    /**
     * Build and return the Eloquent query builder for dynamic mode.
     *
     * Applies the optional filter column, named scopes, and prefix-first
     * LIKE search, then limits the result set to $perPage rows. The caller
     * is responsible for calling ->get() on the returned builder.
     */
    public function resolveOptionsQuery(): Builder
    {
        $query = $this->modelClass::query();

        if ($this->filterColumn && $this->filterValue !== null) {
            $this->applyFilter($query);
        }

        $query = $this->applyScopes($query);

        $search = trim($this->search);
        $query = $this->applySearch($query, $search);

        return $query->limit($this->perPage);
    }

    /**
     * Apply named Eloquent scopes to the given query.
     *
     * Iterates over the normalised $scopes array (array<int, array{scope: string, args: array}>)
     * and calls each scope method on the query, forwarding any args.
     */
    public function applyScopes(Builder $query): Builder
    {
        if ($this->scopes) {
            foreach ($this->scopes as $scopeEntry) {
                $query->{$scopeEntry['scope']}(...$scopeEntry['args']);
            }
        }

        return $query;
    }

    /**
     * Apply prefix-first LIKE search ordering to the query.
     *
     * When $search is non-empty:
     *   - WHERE displayField LIKE %search%
     *   - ORDER BY prefix-match first (CASE WHEN ... LIKE 'search%' THEN 0 ELSE 1 END)
     *   - Secondary ORDER BY displayField ASC
     * When $search is empty:
     *   - ORDER BY displayField ASC only
     */
    public function applySearch(Builder $query, string $search): Builder
    {
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

        return $query;
    }

    /**
     * Filter and return the static options collection based on the current search term.
     *
     * Performs a case-insensitive substring match against displayField.
     * No database queries are issued.
     */
    public function resolveStaticOptions(): Collection
    {
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

    /**
     * Apply the filter column/operator/value constraint to the query.
     *
     * Supports IN, NOT IN, and any standard comparison operator.
     */
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
     *   string                                         → single scope, no args
     *   array<int, string>                             → multiple scopes, no args
     *   array<int, array{scope: string, args: array}>  → fully specified
     *
     * @throws InvalidArgumentException for non-alphanumeric names, unknown scopes, or non-serializable args
     */
    protected function normalizeScopes(mixed $scopes, ?string $modelClass): array
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
}
