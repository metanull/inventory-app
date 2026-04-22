<?php

namespace App\Services\Web;

use App\Models\Collection;
use App\Support\Web\Lists\CollectionListDefinition;
use App\Support\Web\Lists\ListQueryParameters;
use App\Support\Web\Lists\ListState;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

final class CollectionIndexQuery
{
    public function __construct(private readonly CollectionListDefinition $definition) {}

    public function paginate(ListState $state): LengthAwarePaginator
    {
        $query = Collection::query()
            ->select([
                'collections.id',
                'collections.parent_id',
                'collections.language_id',
                'collections.context_id',
                'collections.internal_name',
                'collections.display_order',
                'collections.created_at',
                'collections.updated_at',
            ])
            ->with($this->mapEagerLoads())
            ->withCount('children');

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
        $filters = $state->filters;
        $mode = $filters['mode'] ?? CollectionListDefinition::MODE_HIERARCHY;
        $parentId = $filters['parent_id'] ?? null;

        if ($mode !== CollectionListDefinition::MODE_HIERARCHY) {
            return;
        }

        if (is_string($parentId) && $parentId !== '') {
            $query->childrenOf($parentId);

            return;
        }

        $query->roots();
    }

    private function applySort(Builder $query, ListState $state): void
    {
        $column = $this->definition->sortColumn($state->sort);
        $direction = in_array($state->direction, ListQueryParameters::directions(), true)
            ? $state->direction
            : $this->definition->defaultDirection();

        $query
            ->orderBy($column, $direction)
            ->orderBy('collections.id');
    }

    /**
     * @return array<int, string>
     */
    private function mapEagerLoads(): array
    {
        return array_map(
            static fn (string $relation): string => match ($relation) {
                'context' => 'context:id,internal_name',
                'language' => 'language:id,internal_name',
                default => $relation,
            },
            $this->definition->eagerLoads(),
        );
    }
}
