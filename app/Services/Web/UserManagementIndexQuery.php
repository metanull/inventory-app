<?php

namespace App\Services\Web;

use App\Models\User;
use App\Support\Web\Lists\ListQueryParameters;
use App\Support\Web\Lists\ListState;
use App\Support\Web\Lists\UserManagementListDefinition;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

final class UserManagementIndexQuery
{
    public function __construct(private readonly UserManagementListDefinition $definition) {}

    public function paginate(ListState $state): LengthAwarePaginator
    {
        $query = User::query()
            ->select([
                'users.id',
                'users.name',
                'users.email',
                'users.email_verified_at',
                'users.created_at',
                'users.updated_at',
                'users.two_factor_secret',
                'users.two_factor_confirmed_at',
            ])
            ->with('roles:id,name');

        $this->applySearch($query, $state->search);
        $this->applyFilters($query, $state);
        $this->applySort($query, $state);

        return $query
            ->paginate($state->perPage, ['*'], ListQueryParameters::PAGE, $state->page)
            ->withQueryString();
    }

    private function applySearch(Builder $query, ?string $search): void
    {
        $this->definition->applySearch($query, $search);
    }

    private function applyFilters(Builder $query, ListState $state): void
    {
        $role = $state->filter('role');

        if (! is_string($role) || $role === '') {
            return;
        }

        $query->whereHas('roles', function (Builder $builder) use ($role): void {
            $builder->where('name', $role);
        });
    }

    private function applySort(Builder $query, ListState $state): void
    {
        $column = $this->definition->sortColumn($state->sort);
        $direction = in_array($state->direction, ListQueryParameters::directions(), true)
            ? $state->direction
            : $this->definition->defaultDirection();

        $query
            ->orderBy($column, $direction)
            ->orderBy('users.id');
    }
}
