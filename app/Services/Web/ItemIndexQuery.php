<?php

namespace App\Services\Web;

use App\Models\Item;
use App\Support\Web\Lists\ItemListDefinition;
use App\Support\Web\Lists\ListQueryParameters;
use App\Support\Web\Lists\ListState;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

final class ItemIndexQuery
{
    private const SORT_MAP = [
        'internal_name' => 'items.internal_name',
        'created_at' => 'items.created_at',
        'updated_at' => 'items.updated_at',
    ];

    public function __construct(private readonly ItemListDefinition $definition) {}

    public function paginate(ListState $state): LengthAwarePaginator
    {
        $query = Item::query()
            ->select([
                'items.id',
                'items.parent_id',
                'items.partner_id',
                'items.collection_id',
                'items.country_id',
                'items.project_id',
                'items.internal_name',
                'items.backward_compatibility',
                'items.type',
                'items.created_at',
                'items.updated_at',
            ])
            ->with($this->mapEagerLoads())
            ->withCount('children');

        $this->applySearch($query, $state->search);
        $this->applyFilters($query, $state);
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

        $query->where(function (Builder $builder) use ($search): void {
            $builder
                ->where('items.internal_name', 'like', "%{$search}%")
                ->orWhere('items.backward_compatibility', 'like', "%{$search}%")
                ->orWhereHas('translations', function (Builder $translationQuery) use ($search): void {
                    $translationQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('alternate_name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
        });
    }

    private function applyFilters(Builder $query, ListState $state): void
    {
        $filters = $state->filters;
        $hierarchyMode = (bool) ($filters['hierarchy'] ?? true);
        $parentId = $filters['parent_id'] ?? null;

        foreach (['partner_id', 'collection_id', 'project_id', 'country_id'] as $field) {
            if (! isset($filters[$field])) {
                continue;
            }

            $query->where("items.{$field}", $filters[$field]);
        }

        if (isset($filters['type'])) {
            $query->where('items.type', $filters['type']);
        }

        if (! empty($filters['tags'])) {
            $query->withAllTags($filters['tags']);
        }

        if ($parentId !== null) {
            $query->where('items.parent_id', $parentId);

            return;
        }

        if ($hierarchyMode) {
            $query->parents();
        }
    }

    private function applySort(Builder $query, ListState $state): void
    {
        $column = self::SORT_MAP[$state->sort] ?? self::SORT_MAP[$this->definition->defaultSort()];
        $direction = in_array($state->direction, ListQueryParameters::directions(), true)
            ? $state->direction
            : $this->definition->defaultDirection();

        $query
            ->orderBy($column, $direction)
            ->orderBy('items.id');
    }

    /**
     * @return array<int, string>
     */
    private function mapEagerLoads(): array
    {
        return array_map(
            static fn (string $relation): string => match ($relation) {
                'partner' => 'partner:id,internal_name',
                'collection' => 'collection:id,internal_name',
                'country' => 'country:id,internal_name',
                default => $relation,
            },
            $this->definition->eagerLoads(),
        );
    }
}
