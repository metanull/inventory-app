<?php

namespace App\Services\Web;

use App\Models\GlossaryTranslation;
use App\Support\Web\Lists\GlossaryTranslationListDefinition;
use App\Support\Web\Lists\ListQueryParameters;
use App\Support\Web\Lists\ListState;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

final class GlossaryTranslationIndexQuery
{
    public function __construct(private readonly GlossaryTranslationListDefinition $definition) {}

    /** @return LengthAwarePaginator<int, GlossaryTranslation> */
    public function paginate(ListState $state): LengthAwarePaginator
    {
        $query = GlossaryTranslation::query()
            ->select([
                'glossary_translations.id',
                'glossary_translations.glossary_id',
                'glossary_translations.language_id',
                'glossary_translations.definition',
                'glossary_translations.updated_at',
            ])
            ->join('languages', 'glossary_translations.language_id', '=', 'languages.id')
            ->with(['language:id,internal_name', 'glossary:id,internal_name']);

        $this->applyFilters($query, $state);
        $this->applySort($query, $state);

        return $query
            ->paginate($state->perPage, ['*'], ListQueryParameters::PAGE, $state->page)
            ->withQueryString();
    }

    /** @param Builder<GlossaryTranslation> $query */
    private function applyFilters(Builder $query, ListState $state): void
    {
        $query->where('glossary_translations.glossary_id', $state->filters['glossary_id']);

        if (isset($state->filters['language'])) {
            $query->where('glossary_translations.language_id', $state->filters['language']);
        }
    }

    /** @param Builder<GlossaryTranslation> $query */
    private function applySort(Builder $query, ListState $state): void
    {
        $column = $this->definition->sortColumn($state->sort);
        $direction = in_array($state->direction, ListQueryParameters::directions(), true)
            ? $state->direction
            : $this->definition->defaultDirection();

        $query
            ->orderBy($column, $direction)
            ->orderBy('glossary_translations.id');
    }
}
