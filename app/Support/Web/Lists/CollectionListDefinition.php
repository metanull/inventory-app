<?php

namespace App\Support\Web\Lists;

use Illuminate\Validation\Rule;

final class CollectionListDefinition extends ListDefinition
{
    public const MODE_HIERARCHY = 'hierarchy';

    public const MODE_FLAT = 'flat';

    public function filterParameters(): array
    {
        return ['parent_id', 'mode'];
    }

    public function filterRules(): array
    {
        return [
            'parent_id' => ['sometimes', 'nullable', 'uuid', 'exists:collections,id'],
            'mode' => ['sometimes', 'string', Rule::in([self::MODE_HIERARCHY, self::MODE_FLAT])],
        ];
    }

    public function sorts(): array
    {
        return [
            'internal_name' => new ListSortDefinition('internal_name', ListQueryParameters::ASC),
            'display_order' => new ListSortDefinition('display_order', ListQueryParameters::ASC),
            'created_at' => new ListSortDefinition('created_at', ListQueryParameters::DESC),
            'updated_at' => new ListSortDefinition('updated_at', ListQueryParameters::DESC),
        ];
    }

    public function eagerLoads(): array
    {
        return ['context', 'language'];
    }

    public function normalizeFilters(array $input): array
    {
        return array_filter([
            'parent_id' => $this->normalizeNullableString($input['parent_id'] ?? null),
            'mode' => $this->normalizeMode($input['mode'] ?? null),
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

    private function normalizeMode(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $normalized = strtolower(trim($value));

        return in_array($normalized, [self::MODE_HIERARCHY, self::MODE_FLAT], true) ? $normalized : null;
    }
}
