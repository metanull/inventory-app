<?php

namespace App\Support\Web\Lists;

use Illuminate\Database\Eloquent\Builder;

abstract class ListDefinition
{
    /**
     * @return array<int, string>
     */
    abstract public function filterParameters(): array;

    /**
     * @return array<string, array<int, mixed>>
     */
    abstract public function filterRules(): array;

    /**
     * @return array<string, ListSortDefinition>
     */
    abstract public function sorts(): array;

    /**
     * @return array<int, string>
     */
    public function searchColumns(): array
    {
        return [];
    }

    /**
     * @return array<int, string>
     */
    public function requiredFilterParameters(): array
    {
        return [];
    }

    /**
     * @return array<string, mixed>
     */
    public function normalizeFilters(array $input): array
    {
        return [];
    }

    /**
     * @return array<int, string>
     */
    public function eagerLoads(): array
    {
        return [];
    }

    public function applySearch(Builder $query, ?string $search): void
    {
        if ($search === null) {
            return;
        }

        $columns = $this->searchColumns();

        if ($columns === []) {
            return;
        }

        $query->where(function (Builder $builder) use ($columns, $search): void {
            foreach ($columns as $index => $column) {
                if ($index === 0) {
                    $builder->where($column, 'like', "%{$search}%");

                    continue;
                }

                $builder->orWhere($column, 'like', "%{$search}%");
            }
        });
    }

    public function defaultSort(): string
    {
        return array_key_first($this->sorts()) ?? 'created_at';
    }

    public function defaultDirection(): string
    {
        return $this->sorts()[$this->defaultSort()]->defaultDirection ?? ListQueryParameters::DESC;
    }

    public function sortDefinition(?string $sort = null): ListSortDefinition
    {
        $sortKey = is_string($sort) && array_key_exists($sort, $this->sorts())
            ? $sort
            : $this->defaultSort();

        return $this->sorts()[$sortKey];
    }

    public function sortColumn(?string $sort = null): string
    {
        return $this->sortDefinition($sort)->column;
    }

    /**
     * @return array<int, string>
     */
    public function queryParameters(): array
    {
        return array_merge(ListQueryParameters::canonical(), $this->filterParameters());
    }
}
