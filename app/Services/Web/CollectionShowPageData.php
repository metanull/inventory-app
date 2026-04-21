<?php

namespace App\Services\Web;

use App\Models\Collection;
use App\Models\Item;

class CollectionShowPageData
{
    public function __construct(private readonly TranslationSectionData $translationSectionData) {}

    /**
     * @return array<string, mixed>
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
            'collectionImages' => $collection->collectionImages->values(),
            'translationGroups' => $this->translationSectionData->build($collection->translations),
            'childCollections' => $collection->children->values(),
            'attachableItems' => Item::query()
                ->whereNotIn('id', $collection->attachedItems->pluck('id'))
                ->orderBy('internal_name')
                ->get(),
            'parentOptions' => Collection::query()
                ->whereKeyNot($collection->id)
                ->orderBy('internal_name')
                ->get(),
        ];
    }
}
