<?php

namespace App\Support\Web\Lists;

final class PartnerTranslationImageListDefinition extends ListDefinition
{
    public function filterParameters(): array
    {
        return ['partner_translation_id'];
    }

    public function filterRules(): array
    {
        return [
            'partner_translation_id' => ['sometimes', 'uuid', 'exists:partner_translations,id'],
        ];
    }

    public function requiredFilterParameters(): array
    {
        return ['partner_translation_id'];
    }

    public function sorts(): array
    {
        return [
            'display_order' => new ListSortDefinition('partner_translation_images.display_order', ListQueryParameters::ASC),
            'created_at' => new ListSortDefinition('partner_translation_images.created_at', ListQueryParameters::DESC),
        ];
    }

    public function searchColumns(): array
    {
        return ['partner_translation_images.path', 'partner_translation_images.original_name', 'partner_translation_images.alt_text'];
    }

    public function normalizeFilters(array $input): array
    {
        return array_filter([
            'partner_translation_id' => $this->normalizeNullableString($input['partner_translation_id'] ?? null),
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
