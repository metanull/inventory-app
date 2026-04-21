<?php

namespace Tests\Unit\Services;

use App\Models\Collection;
use App\Models\CollectionImage;
use App\Models\CollectionTranslation;
use App\Models\Context;
use App\Models\Item;
use App\Models\ItemImage;
use App\Models\Language;
use App\Services\Web\CollectionShowPageData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CollectionShowPageDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_build_preloads_all_relations_used_by_collection_show_page(): void
    {
        $defaultLanguage = Language::factory()->withIsDefault()->create(['id' => 'eng']);
        $defaultContext = Context::factory()->withIsDefault()->create();
        $collection = Collection::factory()->create([
            'language_id' => $defaultLanguage->id,
            'context_id' => $defaultContext->id,
        ]);
        Collection::factory()->create([
            'parent_id' => $collection->id,
            'language_id' => $collection->language_id,
            'context_id' => $collection->context_id,
        ]);
        $item = Item::factory()->create();
        $collection->attachItem($item);
        ItemImage::factory()->create(['item_id' => $item->id]);
        CollectionImage::factory()->create(['collection_id' => $collection->id]);
        CollectionTranslation::factory()->forCollection($collection->id)->withLanguage($defaultLanguage->id)->withContext($defaultContext->id)->create();

        DB::flushQueryLog();
        DB::enableQueryLog();

        $collection = $collection->fresh();
        $pageData = app(CollectionShowPageData::class)->build($collection);
        $queryCountAfterBuild = count(DB::getQueryLog());

        $collection->context?->internal_name;
        $collection->language?->internal_name;
        $collection->parent?->internal_name;
        $collection->children->first()?->internal_name;
        $collection->attachedItems->first()?->itemImages->first()?->alt_text;
        $pageData['collectionImages']->first()?->alt_text;
        $pageData['translationGroups']->first()['translations']->first()?->language?->internal_name;
        $pageData['childCollections']->first()?->internal_name;
        $pageData['attachableItems']->first()?->internal_name;
        $pageData['parentOptions']->first()?->internal_name;
        $pageData['childCollections']->first()?->display_order;

        $this->assertCount($queryCountAfterBuild, DB::getQueryLog());
        $this->assertTrue($collection->relationLoaded('context'));
        $this->assertTrue($collection->relationLoaded('language'));
        $this->assertTrue($collection->relationLoaded('parent'));
        $this->assertTrue($collection->relationLoaded('children'));
        $this->assertTrue($collection->relationLoaded('attachedItems'));

        DB::disableQueryLog();
    }
}
