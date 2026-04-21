<?php

namespace App\Support\Web\Lists;

final class AvailableImageListDefinition extends ListDefinition
{
    public function filterParameters(): array
    {
        return [];
    }

    public function filterRules(): array
    {
        return [];
    }

    public function sorts(): array
    {
        return [
            'path' => new ListSortDefinition('available_images.path', ListQueryParameters::ASC),
            'created_at' => new ListSortDefinition('available_images.created_at', ListQueryParameters::DESC),
        ];
    }

    public function searchColumns(): array
    {
        return ['available_images.path', 'available_images.comment'];
    }

    public function normalizeFilters(array $input): array
    {
        return [];
    }
}
