<?php

namespace App\Support\Web\Lists;

final class ItemTranslationListDefinition extends ListDefinition
{
    public function filterParameters(): array
    {
        return ['item_id', 'language', 'context'];
    }

    public function filterRules(): array
    {
        return [
            'item_id' => ['sometimes', 'uuid', 'exists:items,id'],
            'language' => ['sometimes', 'nullable', 'string', 'size:3', 'exists:languages,id'],
            'context' => ['sometimes', 'nullable', 'uuid', 'exists:contexts,id'],
        ];
    }

    public function requiredFilterParameters(): array
    {
        return ['item_id'];
    }

    public function sorts(): array
    {
        return [
            'language.internal_name' => new ListSortDefinition('languages.internal_name', ListQueryParameters::ASC),
            'context.internal_name' => new ListSortDefinition('contexts.internal_name', ListQueryParameters::ASC),
            'updated_at' => new ListSortDefinition('item_translations.updated_at', ListQueryParameters::DESC),
        ];
    }

    public function searchColumns(): array
    {
        return ['item_translations.name', 'item_translations.alternate_name'];
    }

    public function eagerLoads(): array
    {
        return ['language', 'context', 'item'];
    }

    public function normalizeFilters(array $input): array
    {
        return array_filter([
            'item_id' => $this->normalizeNullableString($input['item_id'] ?? null),
            'language' => $this->normalizeNullableString($input['language'] ?? null),
            'context' => $this->normalizeNullableString($input['context'] ?? null),
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
