<?php

namespace App\Support\Web\Lists;

final class TagListDefinition extends ListDefinition
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
            'internal_name' => new ListSortDefinition('internal_name', ListQueryParameters::ASC),
            'description' => new ListSortDefinition('description', ListQueryParameters::ASC),
            'created_at' => new ListSortDefinition('created_at', ListQueryParameters::DESC),
            'updated_at' => new ListSortDefinition('updated_at', ListQueryParameters::DESC),
        ];
    }

    public function searchColumns(): array
    {
        return ['tags.internal_name', 'tags.description'];
    }
}
