<?php

namespace App\Support\Web\Lists;

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

    public function defaultSort(): string
    {
        return array_key_first($this->sorts()) ?? 'created_at';
    }

    public function defaultDirection(): string
    {
        return $this->sorts()[$this->defaultSort()]->defaultDirection ?? ListQueryParameters::DESC;
    }

    /**
     * @return array<int, string>
     */
    public function queryParameters(): array
    {
        return array_merge(ListQueryParameters::canonical(), $this->filterParameters());
    }
}
