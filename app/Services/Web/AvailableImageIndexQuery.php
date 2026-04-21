<?php

namespace App\Services\Web;

use App\Models\AvailableImage;
use App\Support\Web\Lists\AvailableImageListDefinition;
use App\Support\Web\Lists\ListQueryParameters;
use App\Support\Web\Lists\ListState;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

final class AvailableImageIndexQuery
{
    public function __construct(private readonly AvailableImageListDefinition $definition) {}

    public function paginate(ListState $state): LengthAwarePaginator
    {
        $query = AvailableImage::query()
            ->select([
                'available_images.id',
                'available_images.path',
                'available_images.comment',
                'available_images.created_at',
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
            ->orderBy('available_images.id');
    }
}
