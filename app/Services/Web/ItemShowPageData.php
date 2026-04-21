<?php

namespace App\Services\Web;

use App\Models\Context;
use App\Models\Item;
use App\Models\Tag;
use Illuminate\Support\Collection;

class ItemShowPageData
{
    public function __construct(private readonly TranslationSectionData $translationSectionData) {}

    /**
     * @return array<string, mixed>
     */
    public function build(Item $item): array
    {
        $item->load([
            'country',
            'partner',
            'project',
            'parent',
            'children' => fn ($query) => $query->orderBy('display_order'),
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

        return [
            'itemImages' => $item->itemImages->values(),
            'translationGroups' => $this->translationSectionData->build($item->translations),
            'parentOptions' => $relatableItems,
            'childOptions' => $relatableItems
                ->whereNotIn('id', $item->children->pluck('id'))
                ->values(),
            'availableTags' => Tag::query()
                ->whereDoesntHave('items', fn ($query) => $query->where('items.id', $item->id))
                ->orderBy('internal_name')
                ->get(),
            'formattedLinks' => $this->buildFormattedLinks($item),
            'contextOptions' => Context::query()->orderBy('internal_name')->get(),
            'linkTargetOptions' => $relatableItems,
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
