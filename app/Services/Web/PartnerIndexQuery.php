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
    private const SORT_MAP = [
        'internal_name' => 'partners.internal_name',
        'created_at' => 'partners.created_at',
        'updated_at' => 'partners.updated_at',
    ];

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
        $this->applySort($query, $state);

        return $query
            ->paginate($state->perPage, ['*'], ListQueryParameters::PAGE, $state->page)
            ->withQueryString();
    }

    private function applySearch(Builder $query, ?string $search): void
    {
        if ($search === null) {
            return;
        }

        $query->where('partners.internal_name', 'like', "%{$search}%");
    }

    private function applySort(Builder $query, ListState $state): void
    {
        $column = self::SORT_MAP[$state->sort] ?? self::SORT_MAP[$this->definition->defaultSort()];
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
