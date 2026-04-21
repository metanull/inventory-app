<?php

namespace App\Support\Web\Lists;

final class RoleManagementListDefinition extends ListDefinition
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
            'name' => new ListSortDefinition('name', ListQueryParameters::ASC),
            'description' => new ListSortDefinition('description', ListQueryParameters::ASC),
            'created_at' => new ListSortDefinition('created_at', ListQueryParameters::DESC),
        ];
    }

    public function searchColumns(): array
    {
        return ['roles.name', 'roles.description'];
    }
}
