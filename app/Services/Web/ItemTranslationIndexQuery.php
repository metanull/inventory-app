<?php

namespace App\Services\Web;

use App\Models\ItemTranslation;
use App\Support\Web\Lists\ItemTranslationListDefinition;
use App\Support\Web\Lists\ListQueryParameters;
use App\Support\Web\Lists\ListState;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

final class ItemTranslationIndexQuery
{
    public function __construct(private readonly ItemTranslationListDefinition $definition) {}

    public function paginate(ListState $state): LengthAwarePaginator
    {
        $query = ItemTranslation::query()
            ->select([
                'item_translations.id',
                'item_translations.item_id',
                'item_translations.language_id',
                'item_translations.context_id',
                'item_translations.name',
                'item_translations.alternate_name',
                'item_translations.updated_at',
            ])
            ->join('languages', 'item_translations.language_id', '=', 'languages.id')
            ->join('contexts', 'item_translations.context_id', '=', 'contexts.id')
            ->with(['language:id,internal_name', 'context:id,internal_name', 'item:id,internal_name']);

        $this->applyFilters($query, $state);
        $this->applySearch($query, $state->search);
        $this->applySort($query, $state);

        return $query
            ->paginate($state->perPage, ['*'], ListQueryParameters::PAGE, $state->page)
            ->withQueryString();
    }

    private function applyFilters(Builder $query, ListState $state): void
    {
        $query->where('item_translations.item_id', $state->filters['item_id']);

        if (isset($state->filters['language'])) {
            $query->where('item_translations.language_id', $state->filters['language']);
        }

        if (isset($state->filters['context'])) {
            $query->where('item_translations.context_id', $state->filters['context']);
        }
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
            ->orderBy('item_translations.id');
    }
}
