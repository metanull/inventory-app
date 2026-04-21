<?php

namespace App\Support\Web\Lists;

final class CollectionImageListDefinition extends ListDefinition
{
    public function filterParameters(): array
    {
        return ['collection_id'];
    }

    public function filterRules(): array
    {
        return [
            'collection_id' => ['sometimes', 'uuid', 'exists:collections,id'],
        ];
    }

    public function requiredFilterParameters(): array
    {
        return ['collection_id'];
    }

    public function sorts(): array
    {
        return [
            'display_order' => new ListSortDefinition('collection_images.display_order', ListQueryParameters::ASC),
            'created_at' => new ListSortDefinition('collection_images.created_at', ListQueryParameters::DESC),
        ];
    }

    public function searchColumns(): array
    {
        return ['collection_images.path', 'collection_images.original_name', 'collection_images.alt_text'];
    }

    public function normalizeFilters(array $input): array
    {
        return array_filter([
            'collection_id' => $this->normalizeNullableString($input['collection_id'] ?? null),
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
