<?php

namespace App\Support\Web\Lists;

final class CollectionListDefinition extends ListDefinition
{
    public function filterParameters(): array
    {
        return ['context_id', 'language_id', 'parent_id', 'hierarchy'];
    }

    public function filterRules(): array
    {
        return [
            'context_id' => ['sometimes', 'nullable', 'uuid', 'exists:contexts,id'],
            'language_id' => ['sometimes', 'nullable', 'string', 'size:3', 'exists:languages,id'],
            'parent_id' => ['sometimes', 'nullable', 'uuid', 'exists:collections,id'],
            'hierarchy' => ['sometimes', 'boolean'],
        ];
    }

    public function sorts(): array
    {
        return [
            'internal_name' => new ListSortDefinition('internal_name', ListQueryParameters::ASC),
            'display_order' => new ListSortDefinition('display_order', ListQueryParameters::ASC),
            'created_at' => new ListSortDefinition('created_at', ListQueryParameters::DESC),
            'updated_at' => new ListSortDefinition('updated_at', ListQueryParameters::DESC),
        ];
    }

    public function eagerLoads(): array
    {
        return ['context', 'language'];
    }

    public function normalizeFilters(array $input): array
    {
        return array_filter([
            'context_id' => $this->normalizeNullableString($input['context_id'] ?? null),
            'language_id' => $this->normalizeNullableString($input['language_id'] ?? null),
            'parent_id' => $this->normalizeNullableString($input['parent_id'] ?? null),
            'hierarchy' => $this->normalizeBoolean($input['hierarchy'] ?? null),
        ], static fn (mixed $value): bool => $value !== null && $value !== []);
    }

    private function normalizeNullableString(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $normalized = trim($value);

        return $normalized === '' ? null : $normalized;
    }

    private function normalizeBoolean(mixed $value): ?bool
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_bool($value)) {
            return $value;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    }
}
