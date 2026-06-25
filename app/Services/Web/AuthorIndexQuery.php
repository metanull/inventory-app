<?php

namespace App\Services\Web;

use App\Models\Author;
use App\Support\Web\Lists\AuthorListDefinition;
use App\Support\Web\Lists\ListQueryParameters;
use App\Support\Web\Lists\ListState;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

final class AuthorIndexQuery
{
    public function __construct(private readonly AuthorListDefinition $definition) {}

    /** @return LengthAwarePaginator<int, Author> */
    public function paginate(ListState $state): LengthAwarePaginator
    {
        $query = Author::query()->select([
            'authors.id',
            'authors.name',
            'authors.internal_name',
            'authors.created_at',
            'authors.updated_at',
        ]);

        $this->applySearch($query, $state->search);
        $this->applySort($query, $state);

        return $query
            ->paginate($state->perPage, ['*'], ListQueryParameters::PAGE, $state->page)
            ->withQueryString();
    }

    /** @param Builder<Author> $query */
    private function applySearch(Builder $query, ?string $search): void
    {
        $this->definition->applySearch($query, $search);
    }

    /** @param Builder<Author> $query */
    private function applySort(Builder $query, ListState $state): void
    {
        $column = $this->definition->sortColumn($state->sort);
        $direction = in_array($state->direction, ListQueryParameters::directions(), true)
            ? $state->direction
            : $this->definition->defaultDirection();

        $query
            ->orderBy($column, $direction)
            ->orderBy('authors.id');
    }
}
