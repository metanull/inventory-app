<?php

namespace App\Support\Web\Lists;

final class PartnerListDefinition extends ListDefinition
{
    public function filterParameters(): array
    {
        return ['country_id', 'project_id', 'type', 'visible'];
    }

    public function filterRules(): array
    {
        return [
            'country_id' => ['sometimes', 'nullable', 'string', 'size:3', 'exists:countries,id'],
            'project_id' => ['sometimes', 'nullable', 'uuid', 'exists:projects,id'],
            'type' => ['sometimes', 'nullable', 'string', 'max:255'],
            'visible' => ['sometimes', 'boolean'],
        ];
    }

    public function sorts(): array
    {
        return [
            'internal_name' => new ListSortDefinition('internal_name', ListQueryParameters::ASC),
            'created_at' => new ListSortDefinition('created_at', ListQueryParameters::DESC),
            'updated_at' => new ListSortDefinition('updated_at', ListQueryParameters::DESC),
        ];
    }

    public function eagerLoads(): array
    {
        return ['country'];
    }

    public function normalizeFilters(array $input): array
    {
        return array_filter([
            'country_id' => $this->normalizeNullableString($input['country_id'] ?? null),
            'project_id' => $this->normalizeNullableString($input['project_id'] ?? null),
            'type' => $this->normalizeNullableString($input['type'] ?? null),
            'visible' => $this->normalizeBoolean($input['visible'] ?? null),
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
