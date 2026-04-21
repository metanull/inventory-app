<?php

namespace App\Support\Web\Lists;

final class CollectionTranslationListDefinition extends ListDefinition
{
    public function filterParameters(): array
    {
        return ['collection_id', 'language', 'context'];
    }

    public function filterRules(): array
    {
        return [
            'collection_id' => ['sometimes', 'uuid', 'exists:collections,id'],
            'language' => ['sometimes', 'nullable', 'string', 'size:3', 'exists:languages,id'],
            'context' => ['sometimes', 'nullable', 'uuid', 'exists:contexts,id'],
        ];
    }

    public function requiredFilterParameters(): array
    {
        return ['collection_id'];
    }

    public function sorts(): array
    {
        return [
            'language.internal_name' => new ListSortDefinition('languages.internal_name', ListQueryParameters::ASC),
            'context.internal_name' => new ListSortDefinition('contexts.internal_name', ListQueryParameters::ASC),
            'updated_at' => new ListSortDefinition('collection_translations.updated_at', ListQueryParameters::DESC),
        ];
    }

    public function searchColumns(): array
    {
        return ['collection_translations.title', 'collection_translations.description'];
    }

    public function eagerLoads(): array
    {
        return ['language', 'context', 'collection'];
    }

    public function normalizeFilters(array $input): array
    {
        return array_filter([
            'collection_id' => $this->normalizeNullableString($input['collection_id'] ?? null),
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
