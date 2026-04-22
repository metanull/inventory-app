<?php

namespace App\Support\Web\Lists;

final class ListInputNormalizer
{
    /**
     * @return array<string, mixed>
     */
    public function normalize(array $input, ListDefinition $definition): array
    {
        return array_merge([
            ListQueryParameters::SEARCH => $this->normalizeSearch($input[ListQueryParameters::SEARCH] ?? null),
            ListQueryParameters::SORT => $this->normalizeSort($input[ListQueryParameters::SORT] ?? null, $definition),
            ListQueryParameters::DIRECTION => $this->normalizeDirection($input[ListQueryParameters::DIRECTION] ?? null, $definition),
            ListQueryParameters::PAGE => $this->normalizePage($input[ListQueryParameters::PAGE] ?? null),
            ListQueryParameters::PER_PAGE => $this->normalizePerPage($input[ListQueryParameters::PER_PAGE] ?? null),
        ], $definition->normalizeFilters($input));
    }

    private function normalizeSearch(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $normalized = trim($value);

        return $normalized === '' ? null : $normalized;
    }

    private function normalizeSort(mixed $value, ListDefinition $definition): string
    {
        if (! is_string($value)) {
            return $definition->defaultSort();
        }

        $normalized = trim($value);

        return array_key_exists($normalized, $definition->sorts())
            ? $normalized
            : $definition->defaultSort();
    }

    private function normalizeDirection(mixed $value, ListDefinition $definition): string
    {
        if (! is_string($value)) {
            return $definition->defaultDirection();
        }

        $normalized = strtolower(trim($value));

        return in_array($normalized, ListQueryParameters::directions(), true)
            ? $normalized
            : $definition->defaultDirection();
    }

    private function normalizePage(mixed $value): int
    {
        $page = filter_var($value, FILTER_VALIDATE_INT);

        return is_int($page) && $page > 0 ? $page : 1;
    }

    private function normalizePerPage(mixed $value): int
    {
        $options = array_map('intval', (array) config('interface.pagination.per_page_options'));
        $default = (int) config('interface.pagination.default_per_page');
        $perPage = filter_var($value, FILTER_VALIDATE_INT);

        return is_int($perPage) && in_array($perPage, $options, true)
            ? $perPage
            : $default;
    }
}
