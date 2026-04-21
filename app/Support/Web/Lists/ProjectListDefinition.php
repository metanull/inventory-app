<?php

namespace App\Support\Web\Lists;

final class ProjectListDefinition extends ListDefinition
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
            'launch_date' => new ListSortDefinition('launch_date', ListQueryParameters::DESC),
            'is_launched' => new ListSortDefinition('is_launched', ListQueryParameters::DESC),
            'is_enabled' => new ListSortDefinition('is_enabled', ListQueryParameters::DESC),
            'created_at' => new ListSortDefinition('created_at', ListQueryParameters::DESC),
            'updated_at' => new ListSortDefinition('updated_at', ListQueryParameters::DESC),
        ];
    }

    public function searchColumns(): array
    {
        return ['projects.internal_name'];
    }
}
