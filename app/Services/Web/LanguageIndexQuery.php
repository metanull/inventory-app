<?php

namespace App\Services\Web;

use App\Models\Language;
use App\Support\Web\Lists\LanguageListDefinition;
use App\Support\Web\Lists\ListQueryParameters;
use App\Support\Web\Lists\ListState;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

final class LanguageIndexQuery
{
    public function __construct(private readonly LanguageListDefinition $definition) {}

    public function paginate(ListState $state): LengthAwarePaginator
    {
        $query = Language::query()->select([
            'languages.id',
            'languages.internal_name',
            'languages.backward_compatibility',
            'languages.is_default',
            'languages.created_at',
            'languages.updated_at',
        ]);

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
            ->orderBy('languages.id');
    }
}
