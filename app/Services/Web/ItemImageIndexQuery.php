<?php

namespace App\Services\Web;

use App\Models\ItemImage;
use App\Support\Web\Lists\ItemImageListDefinition;
use App\Support\Web\Lists\ListQueryParameters;
use App\Support\Web\Lists\ListState;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

final class ItemImageIndexQuery
{
    public function __construct(private readonly ItemImageListDefinition $definition) {}

    public function paginate(ListState $state): LengthAwarePaginator
    {
        $query = ItemImage::query()
            ->select([
                'item_images.id',
                'item_images.item_id',
                'item_images.path',
                'item_images.original_name',
                'item_images.alt_text',
                'item_images.display_order',
                'item_images.created_at',
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
        $query->where('item_images.item_id', $state->filters['item_id']);
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
            ->orderBy('item_images.id');
    }
}
