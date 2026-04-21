<?php

namespace App\Services\Web;

use App\Models\Tag;
use App\Support\Web\Lists\ListQueryParameters;
use App\Support\Web\Lists\ListState;
use App\Support\Web\Lists\TagListDefinition;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

final class TagIndexQuery
{
    public function __construct(private readonly TagListDefinition $definition) {}

    public function paginate(ListState $state): LengthAwarePaginator
    {
        $query = Tag::query()
            ->select([
                'tags.id',
                'tags.internal_name',
                'tags.description',
                'tags.backward_compatibility',
                'tags.created_at',
                'tags.updated_at',
            ])
            ->withCount('items');

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
            ->orderBy('tags.id');
    }
}
