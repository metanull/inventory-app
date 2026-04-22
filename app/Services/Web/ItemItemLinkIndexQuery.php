<?php

namespace App\Services\Web;

use App\Models\ItemItemLink;
use App\Support\Web\Lists\ItemItemLinkListDefinition;
use App\Support\Web\Lists\ListQueryParameters;
use App\Support\Web\Lists\ListState;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

final class ItemItemLinkIndexQuery
{
    public function __construct(private readonly ItemItemLinkListDefinition $definition) {}

    public function paginate(ListState $state): LengthAwarePaginator
    {
        $query = ItemItemLink::query()
            ->select([
                'item_item_links.id',
                'item_item_links.source_id',
                'item_item_links.target_id',
                'item_item_links.context_id',
                'item_item_links.created_at',
            ])
            ->join('items', 'item_item_links.target_id', '=', 'items.id')
            ->with(['source:id,internal_name', 'target:id,internal_name', 'context:id,internal_name']);

        $this->applyFilters($query, $state);
        $this->applySort($query, $state);

        return $query
            ->paginate($state->perPage, ['*'], ListQueryParameters::PAGE, $state->page)
            ->withQueryString();
    }

    private function applyFilters(Builder $query, ListState $state): void
    {
        $query->where('item_item_links.source_id', $state->filters['item_id']);
    }

    private function applySort(Builder $query, ListState $state): void
    {
        $column = $this->definition->sortColumn($state->sort);
        $direction = in_array($state->direction, ListQueryParameters::directions(), true)
            ? $state->direction
            : $this->definition->defaultDirection();

        $query
            ->orderBy($column, $direction)
            ->orderBy('item_item_links.id');
    }
}
