<?php

namespace App\Support\Web\Lists;

final class GlossarySpellingListDefinition extends ListDefinition
{
    public function filterParameters(): array
    {
        return ['glossary_id'];
    }

    public function filterRules(): array
    {
        return [
            'glossary_id' => ['sometimes', 'uuid', 'exists:glossaries,id'],
        ];
    }

    public function requiredFilterParameters(): array
    {
        return ['glossary_id'];
    }

    public function sorts(): array
    {
        return [
            'spelling' => new ListSortDefinition('glossary_spellings.spelling', ListQueryParameters::ASC),
            'created_at' => new ListSortDefinition('glossary_spellings.created_at', ListQueryParameters::DESC),
        ];
    }

    public function searchColumns(): array
    {
        return ['glossary_spellings.spelling'];
    }

    public function normalizeFilters(array $input): array
    {
        return array_filter([
            'glossary_id' => $this->normalizeNullableString($input['glossary_id'] ?? null),
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
