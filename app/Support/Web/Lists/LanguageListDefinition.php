<?php

namespace App\Support\Web\Lists;

final class LanguageListDefinition extends ListDefinition
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
            'id' => new ListSortDefinition('id', ListQueryParameters::ASC),
            'internal_name' => new ListSortDefinition('internal_name', ListQueryParameters::ASC),
            'is_default' => new ListSortDefinition('is_default', ListQueryParameters::DESC),
            'created_at' => new ListSortDefinition('created_at', ListQueryParameters::DESC),
            'updated_at' => new ListSortDefinition('updated_at', ListQueryParameters::DESC),
        ];
    }

    public function searchColumns(): array
    {
        return ['languages.id', 'languages.internal_name'];
    }
}
