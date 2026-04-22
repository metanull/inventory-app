<?php

namespace App\Services\Web;

use App\Models\Partner;
use App\Support\Web\Lists\ListQueryParameters;
use App\Support\Web\Lists\ListState;
use App\Support\Web\Lists\PartnerListDefinition;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

final class PartnerIndexQuery
{
    public function __construct(private readonly PartnerListDefinition $definition) {}

    public function paginate(ListState $state): LengthAwarePaginator
    {
        $query = Partner::query()
            ->select([
                'partners.id',
                'partners.internal_name',
                'partners.type',
                'partners.country_id',
                'partners.created_at',
                'partners.updated_at',
            ])
            ->with($this->mapEagerLoads());

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

        if (isset($filters['country_id'])) {
            $query->where('partners.country_id', $filters['country_id']);
        }
    }

    private function applySort(Builder $query, ListState $state): void
    {
        $column = $this->definition->sortColumn($state->sort);
        $direction = in_array($state->direction, ListQueryParameters::directions(), true)
            ? $state->direction
            : $this->definition->defaultDirection();

        $query
            ->orderBy($column, $direction)
            ->orderBy('partners.id');
    }

    /**
     * @return array<int, string>
     */
    private function mapEagerLoads(): array
    {
        return array_map(
            static fn (string $relation): string => match ($relation) {
                'country' => 'country:id,internal_name',
                default => $relation,
            },
            $this->definition->eagerLoads(),
        );
    }
}
