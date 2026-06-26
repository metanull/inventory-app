<?php

namespace App\Services\Web;

use App\Enums\ItemType;
use App\Models\Item;
use Illuminate\Database\Eloquent\Relations\Relation;
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
            'children' => function (Relation $query): void {
                $query->with(['itemImages' => function (Relation $imageQuery): void {
                    $imageQuery->orderBy('display_order');
                }])->orderBy('display_order');
            },
            'tags' => function (Relation $query): void { $query->orderBy('internal_name'); },
            'itemImages' => function (Relation $query): void { $query->orderBy('display_order'); },
            'translations.context',
            'translations.language',
            'outgoingLinks.target',
            'outgoingLinks.context',
            'incomingLinks.source',
            'incomingLinks.context',
        ]);

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
                ],
                'children' => [
                    'items' => $structuralChildren,
                ],
                'tags' => [
                    'items' => $item->tags->values(),
                ],
                'links' => [
                    'formatted' => $this->buildFormattedLinks($item),
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
     * @return Collection<int, \stdClass>
     */
    private function buildFormattedLinks(Item $item): Collection
    {
        $links = [];

        foreach ($item->outgoingLinks as $link) {
            $links[] = (object) [
                'id' => $link->id,
                'item' => $link->target,
                'direction' => 'outgoing',
                'link' => $link,
            ];
        }

        foreach ($item->incomingLinks as $link) {
            $links[] = (object) [
                'id' => $link->id,
                'item' => $link->source,
                'direction' => 'incoming',
                'link' => $link,
            ];
        }

        /** @var Collection<int, \stdClass> $result */
        $result = collect($links);

        return $result;
    }
}
