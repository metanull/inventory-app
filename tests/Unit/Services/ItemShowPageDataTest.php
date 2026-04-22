<?php

namespace Tests\Unit\Services;

use App\Models\Collection;
use App\Models\Context;
use App\Models\Country;
use App\Models\Item;
use App\Models\ItemImage;
use App\Models\ItemItemLink;
use App\Models\ItemTranslation;
use App\Models\Language;
use App\Models\Partner;
use App\Models\Project;
use App\Models\Tag;
use App\Services\Web\ItemShowPageData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ItemShowPageDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_build_preloads_all_relations_used_by_item_show_page(): void
    {
        $defaultLanguage = Language::factory()->withIsDefault()->create(['id' => 'eng']);
        $defaultContext = Context::factory()->withIsDefault()->create();
        $partner = Partner::factory()->create();
        $country = Country::factory()->create();
        $project = Project::factory()->create();
        $collection = Collection::factory()->create();

        $item = Item::factory()->create([
            'partner_id' => $partner->id,
            'country_id' => $country->id,
            'project_id' => $project->id,
            'collection_id' => $collection->id,
        ]);

        $parent = Item::factory()->create();
        $item->update(['parent_id' => $parent->id]);
        $child = Item::factory()->Detail()->create(['parent_id' => $item->id]);
        $pictureChild = Item::factory()->Picture()->create(['parent_id' => $item->id]);
        $tag = Tag::factory()->create();
        $item->tags()->attach($tag);
        ItemImage::factory()->create(['item_id' => $item->id]);
        ItemImage::factory()->create(['item_id' => $pictureChild->id]);
        ItemTranslation::factory()->forItem($item->id)->forLanguage($defaultLanguage->id)->forContext($defaultContext->id)->create();
        $target = Item::factory()->create();
        ItemItemLink::factory()->between($item, $target)->create();

        DB::flushQueryLog();
        DB::enableQueryLog();

        $item = $item->fresh();
        $pageData = app(ItemShowPageData::class)->build($item);
        $queryCountAfterBuild = count(DB::getQueryLog());

        $item->country?->internal_name;
        $item->partner?->internal_name;
        $item->project?->internal_name;
        $item->parent?->internal_name;
        $item->children->first()?->internal_name;
        $item->tags->first()?->description;

        $this->assertArrayHasKey('sections', $pageData);
        $this->assertSame(
            ['images', 'pictureChildren', 'translations', 'parent', 'children', 'tags', 'links', 'system'],
            array_keys($pageData['sections'])
        );

        $pageData['sections']['images']['images']->first()?->alt_text;
        $pageData['sections']['pictureChildren']['items']->first()?->itemImages->first()?->alt_text;
        $pageData['sections']['translations']['groups']->first()['translations']->first()?->language?->internal_name;
        $pageData['sections']['links']['formatted']->first()?->item?->internal_name;
        $pageData['sections']['parent']['item']?->internal_name;
        $pageData['sections']['children']['items']->first()?->internal_name;
        $pageData['sections']['system']['id'];

        $this->assertCount($queryCountAfterBuild, DB::getQueryLog());
        $this->assertTrue($item->relationLoaded('country'));
        $this->assertTrue($item->relationLoaded('partner'));
        $this->assertTrue($item->relationLoaded('project'));
        $this->assertTrue($item->relationLoaded('parent'));
        $this->assertTrue($item->relationLoaded('children'));
        $this->assertTrue($item->relationLoaded('tags'));
        $this->assertTrue($pageData['sections']['pictureChildren']['items']->first()->relationLoaded('itemImages'));
        $this->assertTrue($pageData['sections']['pictureChildren']['items']->contains('id', $pictureChild->id));
        $this->assertFalse($pageData['sections']['children']['items']->contains('id', $pictureChild->id));
        $this->assertTrue($pageData['sections']['children']['items']->contains('id', $child->id));

        DB::disableQueryLog();
    }
}
