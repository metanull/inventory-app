<?php

namespace App\Filament\Pages;

use App\Enums\Permission;
use App\Filament\Resources\CollectionResource;
use App\Filament\Resources\ItemResource;
use App\Filament\Support\CollectionDisplayLabel;
use App\Filament\Support\ItemDisplayLabel;
use App\Models\Collection;
use App\Models\CollectionItem;
use App\Models\Item;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class ViewCollectionItemAppearance extends Page
{
    protected static bool $shouldRegisterNavigation = false;

    protected static string $view = 'filament.pages.view-collection-item-appearance';

    public Collection $collection;

    public Item $item;

    public ?int $displayOrder = null;

    /** @var array<string, string> */
    public array $contextualDescriptions = [];

    /** @var array<string, string> */
    public array $sourceBcByLanguage = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->hasPermissionTo(Permission::VIEW_DATA->value) ?? false;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public function mount(Collection $collection, Item $item): void
    {
        abort_unless(static::canAccess(), 403);

        $pivot = CollectionItem::where('collection_id', $collection->id)
            ->where('item_id', $item->id)
            ->first();

        abort_if($pivot === null, 404);

        /** @var Collection|null $foundCollection */
        $foundCollection = CollectionDisplayLabel::withDisplayLabel(
            Collection::where('id', $collection->id)
                ->with(['context:id,internal_name', 'language:id,internal_name', 'parent:id,internal_name'])
        )->first();
        $this->collection = $foundCollection ?? $collection;

        /** @var Item|null $foundItem */
        $foundItem = ItemDisplayLabel::withDisplayLabel(
            Item::where('id', $item->id)
                ->with(['partner:id,internal_name'])
        )->first();
        $this->item = $foundItem ?? $item;

        $this->displayOrder = $pivot->display_order;
        $this->contextualDescriptions = $pivot->contextualDescriptions();
        $this->sourceBcByLanguage = $pivot->sourceBackwardCompatibilityByLanguage();
    }

    public function getTitle(): string|Htmlable
    {
        return 'Collection Appearance';
    }

    /**
     * All language keys that have contextual or provenance data.
     *
     * @return array<int, string>
     */
    public function allLanguageIds(): array
    {
        return array_unique(
            array_merge(
                array_keys($this->contextualDescriptions),
                array_keys($this->sourceBcByLanguage)
            )
        );
    }

    /**
     * URL to the collection's Filament view page (null if not authorized).
     */
    public function collectionUrl(): ?string
    {
        return auth()->user()?->can('view', $this->collection)
            ? CollectionResource::getUrl('view', ['record' => $this->collection])
            : null;
    }

    /**
     * URL to the item's Filament view page (null if not authorized).
     */
    public function itemUrl(): ?string
    {
        return auth()->user()?->can('view', $this->item)
            ? ItemResource::getUrl('view', ['record' => $this->item])
            : null;
    }

    /**
     * Build the URL to the appearance detail page for a given Collection-Item pair.
     */
    public static function getAppearanceUrl(Collection $collection, Item $item): string
    {
        return route('filament.admin.collection-item.appearance', [
            'collection' => $collection->getKey(),
            'item' => $item->getKey(),
        ]);
    }
}
