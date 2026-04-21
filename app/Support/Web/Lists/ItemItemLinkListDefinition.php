<?php

namespace App\Support\Web\Lists;

final class ItemItemLinkListDefinition extends ListDefinition
{
    public function filterParameters(): array
    {
        return ['item_id'];
    }

    public function filterRules(): array
    {
        return [
            'item_id' => ['sometimes', 'uuid', 'exists:items,id'],
        ];
    }

    public function requiredFilterParameters(): array
    {
        return ['item_id'];
    }

    public function sorts(): array
    {
        return [
            'target.internal_name' => new ListSortDefinition('items.internal_name', ListQueryParameters::ASC),
            'created_at' => new ListSortDefinition('item_item_links.created_at', ListQueryParameters::DESC),
        ];
    }

    public function normalizeFilters(array $input): array
    {
        return array_filter([
            'item_id' => $this->normalizeNullableString($input['item_id'] ?? null),
        ], static fn (mixed $value): bool => $value !== null);
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
