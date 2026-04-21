<?php

namespace App\Support\Web\Lists;

final class ListState
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function __construct(
        public readonly ?string $search,
        public readonly string $sort,
        public readonly string $direction,
        public readonly int $page,
        public readonly int $perPage,
        public readonly array $filters = [],
    ) {}

    public function hasFilter(string $key): bool
    {
        return array_key_exists($key, $this->filters);
    }

    public function filter(string $key, mixed $default = null): mixed
    {
        return $this->hasFilter($key) ? $this->filters[$key] : $default;
    }

    /**
     * @return array<string, mixed>
     */
    public function query(array $except = []): array
    {
        $query = array_merge($this->filters, [
            ListQueryParameters::SEARCH => $this->search,
            ListQueryParameters::SORT => $this->sort,
            ListQueryParameters::DIRECTION => $this->direction,
            ListQueryParameters::PAGE => $this->page,
            ListQueryParameters::PER_PAGE => $this->perPage,
        ]);

        foreach ($except as $key) {
            unset($query[$key]);
        }

        return array_filter($query, static fn (mixed $value): bool => $value !== null && $value !== [] && $value !== '');
    }
}
