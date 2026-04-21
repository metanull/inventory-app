<?php

namespace App\Services\Web;

use App\Models\Project;
use App\Support\Web\Lists\ListQueryParameters;
use App\Support\Web\Lists\ListState;
use App\Support\Web\Lists\ProjectListDefinition;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

final class ProjectIndexQuery
{
    public function __construct(private readonly ProjectListDefinition $definition) {}

    public function paginate(ListState $state): LengthAwarePaginator
    {
        $query = Project::query()->select([
            'projects.id',
            'projects.internal_name',
            'projects.launch_date',
            'projects.is_launched',
            'projects.is_enabled',
            'projects.created_at',
            'projects.updated_at',
        ]);

        $this->applySearch($query, $state->search);
        $this->applySort($query, $state);

        return $query
            ->paginate($state->perPage, ['*'], ListQueryParameters::PAGE, $state->page)
            ->withQueryString();
    }

    private function applySearch(Builder $query, ?string $search): void
    {
        $this->definition->applySearch($query, $search);
    }

    private function applySort(Builder $query, ListState $state): void
    {
        $column = $this->definition->sortColumn($state->sort);
        $direction = in_array($state->direction, ListQueryParameters::directions(), true)
            ? $state->direction
            : $this->definition->defaultDirection();

        $query
            ->orderBy($column, $direction)
            ->orderBy('projects.id');
    }
}
