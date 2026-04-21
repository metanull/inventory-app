<?php

namespace App\Services\Web;

use App\Models\Context;
use App\Support\Web\Lists\ContextListDefinition;
use App\Support\Web\Lists\ListQueryParameters;
use App\Support\Web\Lists\ListState;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

final class ContextIndexQuery
{
    public function __construct(private readonly ContextListDefinition $definition) {}

    public function paginate(ListState $state): LengthAwarePaginator
    {
        $query = Context::query()->select([
            'contexts.id',
            'contexts.internal_name',
            'contexts.is_default',
            'contexts.created_at',
            'contexts.updated_at',
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
            ->orderBy('contexts.id');
    }
}
