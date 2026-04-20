<?php

namespace App\Support\Web\Lists;

use App\Enums\ItemType;
use Illuminate\Validation\Rule;

final class ItemListDefinition extends ListDefinition
{
    public function filterParameters(): array
    {
        return ['partner_id', 'collection_id', 'project_id', 'country_id', 'parent_id', 'type', 'hierarchy', 'tags'];
    }

    public function filterRules(): array
    {
        return [
            'partner_id' => ['sometimes', 'nullable', 'uuid', 'exists:partners,id'],
            'collection_id' => ['sometimes', 'nullable', 'uuid', 'exists:collections,id'],
            'project_id' => ['sometimes', 'nullable', 'uuid', 'exists:projects,id'],
            'country_id' => ['sometimes', 'nullable', 'string', 'size:3', 'exists:countries,id'],
            'parent_id' => ['sometimes', 'nullable', 'uuid', 'exists:items,id'],
            'type' => ['sometimes', 'nullable', Rule::in(array_map(static fn (ItemType $type): string => $type->value, ItemType::cases()))],
            'hierarchy' => ['sometimes', 'boolean'],
            'tags' => ['sometimes', 'array'],
            'tags.*' => ['uuid', 'exists:tags,id'],
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

    public function normalizeFilters(array $input): array
    {
        return array_filter([
            'partner_id' => $this->normalizeNullableString($input['partner_id'] ?? null),
            'collection_id' => $this->normalizeNullableString($input['collection_id'] ?? null),
            'project_id' => $this->normalizeNullableString($input['project_id'] ?? null),
            'country_id' => $this->normalizeNullableString($input['country_id'] ?? null),
            'parent_id' => $this->normalizeNullableString($input['parent_id'] ?? null),
            'type' => $this->normalizeType($input['type'] ?? null),
            'hierarchy' => $this->normalizeBoolean($input['hierarchy'] ?? null),
            'tags' => $this->normalizeStringArray($input['tags'] ?? null),
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

    private function normalizeType(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $normalized = strtolower(trim($value));

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

    /**
     * @return array<int, string>
     */
    private function normalizeStringArray(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        $normalized = array_values(array_filter(array_map(
            static fn (mixed $item): ?string => is_string($item) && trim($item) !== '' ? trim($item) : null,
            $value,
        )));

        return array_values(array_unique($normalized));
    }
}
