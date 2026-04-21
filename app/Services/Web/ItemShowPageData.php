<?php

namespace App\Services\Web;

use App\Enums\ItemType;
use App\Models\Context;
use App\Models\Item;
use App\Models\Tag;
use App\Support\Web\TagPresentation;
use Illuminate\Support\Collection;

class ItemShowPageData
{
    public function __construct(private readonly TranslationSectionData $translationSectionData) {}

    /**
     * @return array{sections: array<string, array<string, mixed>>}
     */
    public function build(Item $item): array
    {
        $item->load([
            'country',
            'partner',
            'project',
            'parent',
            'children' => fn ($query) => $query
                ->with(['itemImages' => fn ($imageQuery) => $imageQuery->orderBy('display_order')])
                ->orderBy('display_order'),
            'tags' => fn ($query) => $query->orderBy('internal_name'),
            'itemImages' => fn ($query) => $query->orderBy('display_order'),
            'translations.context',
            'translations.language',
            'outgoingLinks.target',
            'outgoingLinks.context',
            'incomingLinks.source',
            'incomingLinks.context',
        ]);

        $relatableItems = Item::query()
            ->whereKeyNot($item->id)
            ->orderBy('internal_name')
            ->get();

        $pictureChildren = $item->children
            ->filter(fn (Item $child): bool => $child->type === ItemType::PICTURE)
            ->values();

        $structuralChildren = $item->children
            ->reject(fn (Item $child): bool => $child->type === ItemType::PICTURE)
            ->values();

        return [
            'sections' => [
                'images' => [
                    'images' => $item->itemImages->values(),
                ],
                'pictureChildren' => [
                    'items' => $pictureChildren,
                ],
                'translations' => [
                    'groups' => $this->translationSectionData->build($item->translations),
                ],
                'parent' => [
                    'item' => $item->parent,
                    'options' => $relatableItems,
                ],
                'children' => [
                    'items' => $structuralChildren,
                    'options' => $relatableItems
                        ->whereNotIn('id', $item->children->pluck('id'))
                        ->values(),
                ],
                'tags' => [
                    'items' => $item->tags->values(),
                    'availableTags' => Tag::query()
                        ->whereDoesntHave('items', fn ($query) => $query->where('items.id', $item->id))
                        ->orderBy('internal_name')
                        ->get()
                        ->map(function (Tag $tag): Tag {
                            $tag->setAttribute('display_label', TagPresentation::label($tag));

                            return $tag;
                        })
                        ->sortBy('display_label')
                        ->values(),
                ],
                'links' => [
                    'formatted' => $this->buildFormattedLinks($item),
                    'contextOptions' => Context::query()->orderBy('internal_name')->get(),
                    'targetOptions' => $relatableItems,
                ],
                'system' => [
                    'id' => $item->id,
                    'backwardCompatibilityId' => $item->backward_compatibility,
                    'createdAt' => $item->created_at,
                    'updatedAt' => $item->updated_at,
                ],
            ],
        ];
    }

    /**
     * @return Collection<int, object>
     */
    private function buildFormattedLinks(Item $item)
    {
        $formattedLinks = collect();

        foreach ($item->outgoingLinks as $link) {
            $formattedLinks->push((object) [
                'id' => $link->id,
                'item' => $link->target,
                'direction' => 'outgoing',
                'link' => $link,
            ]);
        }

        foreach ($item->incomingLinks as $link) {
            $formattedLinks->push((object) [
                'id' => $link->id,
                'item' => $link->source,
                'direction' => 'incoming',
                'link' => $link,
            ]);
        }

        return $formattedLinks;
    }
}
