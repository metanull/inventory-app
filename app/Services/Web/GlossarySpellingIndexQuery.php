<?php

namespace App\Services\Web;

use App\Models\GlossarySpelling;
use App\Support\Web\Lists\GlossarySpellingListDefinition;
use App\Support\Web\Lists\ListQueryParameters;
use App\Support\Web\Lists\ListState;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

final class GlossarySpellingIndexQuery
{
    public function __construct(private readonly GlossarySpellingListDefinition $definition) {}

    public function paginate(ListState $state): LengthAwarePaginator
    {
        $query = GlossarySpelling::query()
            ->select([
                'glossary_spellings.id',
                'glossary_spellings.glossary_id',
                'glossary_spellings.language_id',
                'glossary_spellings.spelling',
                'glossary_spellings.created_at',
            ])
            ->join('languages', 'glossary_spellings.language_id', '=', 'languages.id')
            ->with(['language:id,internal_name', 'glossary:id,internal_name']);

        $this->applyFilters($query, $state);
        $this->applySearch($query, $state->search);
        $this->applySort($query, $state);

        return $query
            ->paginate($state->perPage, ['*'], ListQueryParameters::PAGE, $state->page)
            ->withQueryString();
    }

    private function applyFilters(Builder $query, ListState $state): void
    {
        $query->where('glossary_spellings.glossary_id', $state->filters['glossary_id']);
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
            ->orderBy('glossary_spellings.id');
    }
}
