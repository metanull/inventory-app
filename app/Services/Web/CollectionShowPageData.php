<?php

namespace App\Services\Web;

use App\Models\Collection;
use Illuminate\Database\Eloquent\Builder;

class CollectionShowPageData
{
    public function __construct(private readonly TranslationSectionData $translationSectionData) {}

    /**
     * @return array{sections: array<string, array<string, mixed>>}
     */
    public function build(Collection $collection): array
    {
        $collection->load([
            'context',
            'language',
            'parent',
            'children' => fn (Builder $query): Builder => $query->orderBy('display_order'),
            'translations.context',
            'translations.language',
            'attachedItems.itemImages' => fn (Builder $query): Builder => $query->orderBy('display_order'),
            'collectionImages' => fn (Builder $query): Builder => $query->orderBy('display_order'),
        ]);

        return [
            'sections' => [
                'images' => [
                    'images' => $collection->collectionImages->values(),
                ],
                'children' => [
                    'items' => $collection->children->values(),
                ],
                'items' => [
                    'items' => $collection->attachedItems->values(),
                ],
                'translations' => [
                    'groups' => $this->translationSectionData->build($collection->translations),
                ],
                'parent' => [
                    'collection' => $collection->parent,
                ],
                'system' => [
                    'id' => $collection->id,
                    'backwardCompatibilityId' => $collection->backward_compatibility,
                    'createdAt' => $collection->created_at,
                    'updatedAt' => $collection->updated_at,
                ],
            ],
        ];
    }
}
