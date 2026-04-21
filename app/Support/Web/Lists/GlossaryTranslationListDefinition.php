<?php

namespace App\Support\Web\Lists;

final class GlossaryTranslationListDefinition extends ListDefinition
{
    public function filterParameters(): array
    {
        return ['glossary_id', 'language'];
    }

    public function filterRules(): array
    {
        return [
            'glossary_id' => ['sometimes', 'uuid', 'exists:glossaries,id'],
            'language' => ['sometimes', 'nullable', 'string', 'size:3', 'exists:languages,id'],
        ];
    }

    public function requiredFilterParameters(): array
    {
        return ['glossary_id'];
    }

    public function sorts(): array
    {
        return [
            'language.internal_name' => new ListSortDefinition('languages.internal_name', ListQueryParameters::ASC),
            'updated_at' => new ListSortDefinition('glossary_translations.updated_at', ListQueryParameters::DESC),
        ];
    }

    public function normalizeFilters(array $input): array
    {
        return array_filter([
            'glossary_id' => $this->normalizeNullableString($input['glossary_id'] ?? null),
            'language' => $this->normalizeNullableString($input['language'] ?? null),
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
