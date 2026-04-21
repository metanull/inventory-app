<?php

namespace App\Services\Web;

use App\Models\Collection;
use App\Models\Item;

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
            'children' => fn ($query) => $query->orderBy('display_order'),
            'translations.context',
            'translations.language',
            'attachedItems.itemImages' => fn ($query) => $query->orderBy('display_order'),
            'collectionImages' => fn ($query) => $query->orderBy('display_order'),
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
                    'attachableItems' => Item::query()
                        ->whereNotIn('id', $collection->attachedItems->pluck('id'))
                        ->orderBy('internal_name')
                        ->get(),
                ],
                'translations' => [
                    'groups' => $this->translationSectionData->build($collection->translations),
                ],
                'parent' => [
                    'collection' => $collection->parent,
                    'options' => Collection::query()
                        ->whereKeyNot($collection->id)
                        ->orderBy('internal_name')
                        ->get(),
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
