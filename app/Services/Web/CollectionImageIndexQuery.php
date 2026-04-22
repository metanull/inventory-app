<?php

namespace App\Services\Web;

use App\Models\CollectionImage;
use App\Support\Web\Lists\CollectionImageListDefinition;
use App\Support\Web\Lists\ListQueryParameters;
use App\Support\Web\Lists\ListState;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

final class CollectionImageIndexQuery
{
    public function __construct(private readonly CollectionImageListDefinition $definition) {}

    public function paginate(ListState $state): LengthAwarePaginator
    {
        $query = CollectionImage::query()
            ->select([
                'collection_images.id',
                'collection_images.collection_id',
                'collection_images.path',
                'collection_images.original_name',
                'collection_images.alt_text',
                'collection_images.display_order',
                'collection_images.created_at',
            ]);

        $this->applyFilters($query, $state);
        $this->applySearch($query, $state->search);
        $this->applySort($query, $state);

        return $query
            ->paginate($state->perPage, ['*'], ListQueryParameters::PAGE, $state->page)
            ->withQueryString();
    }

    private function applyFilters(Builder $query, ListState $state): void
    {
        $query->where('collection_images.collection_id', $state->filters['collection_id']);
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
            ->orderBy('collection_images.id');
    }
}
