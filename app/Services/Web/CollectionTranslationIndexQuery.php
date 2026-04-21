<?php

namespace App\Services\Web;

use App\Models\CollectionTranslation;
use App\Support\Web\Lists\CollectionTranslationListDefinition;
use App\Support\Web\Lists\ListQueryParameters;
use App\Support\Web\Lists\ListState;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

final class CollectionTranslationIndexQuery
{
    public function __construct(private readonly CollectionTranslationListDefinition $definition) {}

    public function paginate(ListState $state): LengthAwarePaginator
    {
        $query = CollectionTranslation::query()
            ->select([
                'collection_translations.id',
                'collection_translations.collection_id',
                'collection_translations.language_id',
                'collection_translations.context_id',
                'collection_translations.title',
                'collection_translations.description',
                'collection_translations.updated_at',
            ])
            ->join('languages', 'collection_translations.language_id', '=', 'languages.id')
            ->join('contexts', 'collection_translations.context_id', '=', 'contexts.id')
            ->with(['language:id,internal_name', 'context:id,internal_name', 'collection:id,internal_name']);

        $this->applyFilters($query, $state);
        $this->applySearch($query, $state->search);
        $this->applySort($query, $state);

        return $query
            ->paginate($state->perPage, ['*'], ListQueryParameters::PAGE, $state->page)
            ->withQueryString();
    }

    private function applyFilters(Builder $query, ListState $state): void
    {
        $query->where('collection_translations.collection_id', $state->filters['collection_id']);

        if (isset($state->filters['language'])) {
            $query->where('collection_translations.language_id', $state->filters['language']);
        }

        if (isset($state->filters['context'])) {
            $query->where('collection_translations.context_id', $state->filters['context']);
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
            ->orderBy('collection_translations.id');
    }
}
