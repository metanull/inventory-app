<?php

namespace App\Support\Web\Lists;

final class PartnerImageListDefinition extends ListDefinition
{
    public function filterParameters(): array
    {
        return ['partner_id'];
    }

    public function filterRules(): array
    {
        return [
            'partner_id' => ['sometimes', 'uuid', 'exists:partners,id'],
        ];
    }

    public function requiredFilterParameters(): array
    {
        return ['partner_id'];
    }

    public function sorts(): array
    {
        return [
            'display_order' => new ListSortDefinition('partner_images.display_order', ListQueryParameters::ASC),
            'created_at' => new ListSortDefinition('partner_images.created_at', ListQueryParameters::DESC),
        ];
    }

    public function searchColumns(): array
    {
        return ['partner_images.path', 'partner_images.original_name', 'partner_images.alt_text'];
    }

    public function normalizeFilters(array $input): array
    {
        return array_filter([
            'partner_id' => $this->normalizeNullableString($input['partner_id'] ?? null),
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
