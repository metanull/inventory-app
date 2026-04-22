<?php

namespace App\Services\Web;

use App\Models\PartnerImage;
use App\Support\Web\Lists\ListQueryParameters;
use App\Support\Web\Lists\ListState;
use App\Support\Web\Lists\PartnerImageListDefinition;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

final class PartnerImageIndexQuery
{
    public function __construct(private readonly PartnerImageListDefinition $definition) {}

    public function paginate(ListState $state): LengthAwarePaginator
    {
        $query = PartnerImage::query()
            ->select([
                'partner_images.id',
                'partner_images.partner_id',
                'partner_images.path',
                'partner_images.original_name',
                'partner_images.alt_text',
                'partner_images.display_order',
                'partner_images.created_at',
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
        $query->where('partner_images.partner_id', $state->filters['partner_id']);
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
            ->orderBy('partner_images.id');
    }
}
