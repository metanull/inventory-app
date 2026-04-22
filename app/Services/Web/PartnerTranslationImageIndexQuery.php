<?php

namespace App\Services\Web;

use App\Models\PartnerTranslationImage;
use App\Support\Web\Lists\ListQueryParameters;
use App\Support\Web\Lists\ListState;
use App\Support\Web\Lists\PartnerTranslationImageListDefinition;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

final class PartnerTranslationImageIndexQuery
{
    public function __construct(private readonly PartnerTranslationImageListDefinition $definition) {}

    public function paginate(ListState $state): LengthAwarePaginator
    {
        $query = PartnerTranslationImage::query()
            ->select([
                'partner_translation_images.id',
                'partner_translation_images.partner_translation_id',
                'partner_translation_images.path',
                'partner_translation_images.original_name',
                'partner_translation_images.alt_text',
                'partner_translation_images.display_order',
                'partner_translation_images.created_at',
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
        $query->where('partner_translation_images.partner_translation_id', $state->filters['partner_translation_id']);
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
            ->orderBy('partner_translation_images.id');
    }
}
