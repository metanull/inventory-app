<?php

namespace App\Support\Web\Lists;

final class PartnerListDefinition extends ListDefinition
{
    public function filterParameters(): array
    {
        return ['country_id'];
    }

    public function filterRules(): array
    {
        return [
            'country_id' => ['sometimes', 'nullable', 'string', 'size:3', 'exists:countries,id'],
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

    public function searchColumns(): array
    {
        return ['partners.internal_name'];
    }

    public function eagerLoads(): array
    {
        return ['country'];
    }

    public function normalizeFilters(array $input): array
    {
        return array_filter([
            'country_id' => $this->normalizeNullableString($input['country_id'] ?? null),
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
}
