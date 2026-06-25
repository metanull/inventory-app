<?php

namespace App\Services\Web;

use App\Support\Web\Lists\ListQueryParameters;
use App\Support\Web\Lists\ListState;
use App\Support\Web\Lists\RoleManagementListDefinition;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Role;

final class RoleManagementIndexQuery
{
    public function __construct(private readonly RoleManagementListDefinition $definition) {}

    /** @return LengthAwarePaginator<int, Role> */
    public function paginate(ListState $state): LengthAwarePaginator
    {
        $query = Role::query()
            ->select([
                'roles.id',
                'roles.name',
                'roles.description',
                'roles.guard_name',
                'roles.created_at',
                'roles.updated_at',
            ])
            ->withCount(['permissions', 'users']);

        $this->applySearch($query, $state->search);
        $this->applySort($query, $state);

        return $query
            ->paginate($state->perPage, ['*'], ListQueryParameters::PAGE, $state->page)
            ->withQueryString();
    }

    /** @param Builder<Role> $query */
    private function applySearch(Builder $query, ?string $search): void
    {
        $this->definition->applySearch($query, $search);
    }

    /** @param Builder<Role> $query */
    private function applySort(Builder $query, ListState $state): void
    {
        $column = $this->definition->sortColumn($state->sort);
        $direction = in_array($state->direction, ListQueryParameters::directions(), true)
            ? $state->direction
            : $this->definition->defaultDirection();

        $query
            ->orderBy($column, $direction)
            ->orderBy('roles.id');
    }
}
