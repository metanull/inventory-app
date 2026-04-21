<?php

namespace App\Services\Web;

use App\Models\PartnerTranslation;
use App\Support\Web\Lists\ListQueryParameters;
use App\Support\Web\Lists\ListState;
use App\Support\Web\Lists\PartnerTranslationListDefinition;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

final class PartnerTranslationIndexQuery
{
    public function __construct(private readonly PartnerTranslationListDefinition $definition) {}

    public function paginate(ListState $state): LengthAwarePaginator
    {
        $query = PartnerTranslation::query()
            ->select([
                'partner_translations.id',
                'partner_translations.partner_id',
                'partner_translations.language_id',
                'partner_translations.context_id',
                'partner_translations.name',
                'partner_translations.description',
                'partner_translations.updated_at',
            ])
            ->join('languages', 'partner_translations.language_id', '=', 'languages.id')
            ->join('contexts', 'partner_translations.context_id', '=', 'contexts.id')
            ->with(['language:id,internal_name', 'context:id,internal_name', 'partner:id,internal_name']);

        $this->applyFilters($query, $state);
        $this->applySearch($query, $state->search);
        $this->applySort($query, $state);

        return $query
            ->paginate($state->perPage, ['*'], ListQueryParameters::PAGE, $state->page)
            ->withQueryString();
    }

    private function applyFilters(Builder $query, ListState $state): void
    {
        $query->where('partner_translations.partner_id', $state->filters['partner_id']);

        if (isset($state->filters['language'])) {
            $query->where('partner_translations.language_id', $state->filters['language']);
        }

        if (isset($state->filters['context'])) {
            $query->where('partner_translations.context_id', $state->filters['context']);
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
            ->orderBy('partner_translations.id');
    }
}
