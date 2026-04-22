<?php

namespace App\Services\Web;

use App\Models\Glossary;
use App\Support\Web\Lists\GlossaryListDefinition;
use App\Support\Web\Lists\ListQueryParameters;
use App\Support\Web\Lists\ListState;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

final class GlossaryIndexQuery
{
    public function __construct(private readonly GlossaryListDefinition $definition) {}

    public function paginate(ListState $state): LengthAwarePaginator
    {
        $query = Glossary::query()
            ->select([
                'glossaries.id',
                'glossaries.internal_name',
                'glossaries.backward_compatibility',
                'glossaries.created_at',
                'glossaries.updated_at',
            ])
            ->with($this->mapEagerLoads());

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
            ->orderBy('glossaries.id');
    }

    private function mapEagerLoads(): array
    {
        return array_map(
            static fn (string $relation): string => match ($relation) {
                'translations' => 'translations:id,glossary_id,language_id',
                'spellings' => 'spellings:id,glossary_id,language_id',
                default => $relation,
            },
            $this->definition->eagerLoads(),
        );
    }
}
