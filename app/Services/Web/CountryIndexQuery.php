<?php

namespace App\Services\Web;

use App\Models\Country;
use App\Support\Web\Lists\CountryListDefinition;
use App\Support\Web\Lists\ListQueryParameters;
use App\Support\Web\Lists\ListState;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

final class CountryIndexQuery
{
    public function __construct(private readonly CountryListDefinition $definition) {}

    public function paginate(ListState $state): LengthAwarePaginator
    {
        $query = Country::query()->select([
            'countries.id',
            'countries.internal_name',
            'countries.backward_compatibility',
            'countries.created_at',
            'countries.updated_at',
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
            ->orderBy('countries.id');
    }
}
