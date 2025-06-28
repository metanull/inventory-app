<?php

namespace Tests\Unit\Item;

use App\Models\Item;
use App\Models\Tag;
use App\Models\TagItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class FactoryTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_factory(): void
    {
        $item = Item::factory()->create();
        $this->assertInstanceOf(Item::class, $item);
        $this->assertNotEmpty($item->id);
        $this->assertNotEmpty($item->internal_name);
        $this->assertNotEmpty($item->backward_compatibility);
        $this->assertNotEmpty($item->type);
    }

    public function test_factory_object(): void
    {
        $item = Item::factory()->Object()->make();
        $this->assertInstanceOf(Item::class, $item);
        $this->assertEquals('object', $item->type);
    }

    public function test_factory_monument(): void
    {
        $item = Item::factory()->Monument()->make();
        $this->assertInstanceOf(Item::class, $item);
        $this->assertEquals('monument', $item->type);
    }

    public function test_factory_with_partner(): void
    {
        $item = Item::factory()->withPartner()->make();
        $this->assertNotNull($item->partner_id);
        $this->assertInstanceOf(Item::class, $item);
    }

    public function test_factory_with_country(): void
    {
        $item = Item::factory()->withCountry()->make();
        $this->assertNotNull($item->country_id);
        $this->assertInstanceOf(Item::class, $item);
    }

    public function test_factory_with_project(): void
    {
        $item = Item::factory()->withProject()->make();
        $this->assertNotNull($item->project_id);
        $this->assertInstanceOf(Item::class, $item);
    }

    public function test_factory_creates_a_row_in_database(): void
    {
        $item = Item::factory()->create();
        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'internal_name' => $item->internal_name,
            'backward_compatibility' => $item->backward_compatibility,
            'partner_id' => null,
            'country_id' => null,
            'project_id' => null,
            'type' => $item->type,
        ]);
    }

    public function test_factory_creates_a_row_in_database_object(): void
    {
        $item = Item::factory()->Object()->create();
        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'type' => 'object',
        ]);
    }

    public function test_factory_creates_a_row_in_database_monument(): void
    {
        $item = Item::factory()->Monument()->create();
        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'type' => 'monument',
        ]);
    }

    public function test_factory_creates_a_row_in_database_with_partner(): void
    {
        $item = Item::factory()->withPartner()->create();
        $this->assertNotNull($item->partner_id);
        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'partner_id' => $item->partner->id,
        ]);
    }

    public function test_factory_creates_a_row_in_database_with_country(): void
    {
        $item = Item::factory()->withCountry()->create();
        $this->assertNotNull($item->country_id);
        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'country_id' => $item->country->id,
        ]);
    }

    public function test_factory_creates_a_row_in_database_with_project(): void
    {
        $item = Item::factory()->withProject()->create();
        $this->assertNotNull($item->project_id);
        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'project_id' => $item->project->id,
        ]);
    }

    public function test_scope_for_tag_returns_items_for_specific_tag(): void
    {
        $tag1 = Tag::factory()->create();
        $tag2 = Tag::factory()->create();
        $item1 = Item::factory()->create();
        $item2 = Item::factory()->create();
        $item3 = Item::factory()->create();

        // Associate items with tags
        TagItem::factory()->create(['tag_id' => $tag1->id, 'item_id' => $item1->id]);
        TagItem::factory()->create(['tag_id' => $tag1->id, 'item_id' => $item2->id]);
        TagItem::factory()->create(['tag_id' => $tag2->id, 'item_id' => $item3->id]);

        $itemsForTag1 = Item::forTag($tag1)->get();
        $itemsForTag2 = Item::forTag($tag2)->get();

        $this->assertCount(2, $itemsForTag1);
        $this->assertCount(1, $itemsForTag2);
        $this->assertTrue($itemsForTag1->contains('id', $item1->id));
        $this->assertTrue($itemsForTag1->contains('id', $item2->id));
        $this->assertFalse($itemsForTag1->contains('id', $item3->id));
        $this->assertTrue($itemsForTag2->contains('id', $item3->id));
    }

    public function test_scope_with_all_tags_returns_items_with_all_specified_tags(): void
    {
        $tag1 = Tag::factory()->create();
        $tag2 = Tag::factory()->create();
        $tag3 = Tag::factory()->create();
        $item1 = Item::factory()->create();
        $item2 = Item::factory()->create();
        $item3 = Item::factory()->create();

        // Item1 has tag1 and tag2
        TagItem::factory()->create(['tag_id' => $tag1->id, 'item_id' => $item1->id]);
        TagItem::factory()->create(['tag_id' => $tag2->id, 'item_id' => $item1->id]);

        // Item2 has only tag1
        TagItem::factory()->create(['tag_id' => $tag1->id, 'item_id' => $item2->id]);

        // Item3 has tag2 and tag3
        TagItem::factory()->create(['tag_id' => $tag2->id, 'item_id' => $item3->id]);
        TagItem::factory()->create(['tag_id' => $tag3->id, 'item_id' => $item3->id]);

        $itemsWithBothTags = Item::withAllTags([$tag1->id, $tag2->id])->get();

        $this->assertCount(1, $itemsWithBothTags);
        $this->assertTrue($itemsWithBothTags->contains('id', $item1->id));
    }

    public function test_scope_with_any_tags_returns_items_with_any_specified_tags(): void
    {
        $tag1 = Tag::factory()->create();
        $tag2 = Tag::factory()->create();
        $tag3 = Tag::factory()->create();
        $item1 = Item::factory()->create();
        $item2 = Item::factory()->create();
        $item3 = Item::factory()->create();

        // Item1 has tag1
        TagItem::factory()->create(['tag_id' => $tag1->id, 'item_id' => $item1->id]);

        // Item2 has tag2
        TagItem::factory()->create(['tag_id' => $tag2->id, 'item_id' => $item2->id]);

        // Item3 has tag3 (not in our search)
        TagItem::factory()->create(['tag_id' => $tag3->id, 'item_id' => $item3->id]);

        $itemsWithAnyTags = Item::withAnyTags([$tag1->id, $tag2->id])->get();

        $this->assertCount(2, $itemsWithAnyTags);
        $this->assertTrue($itemsWithAnyTags->contains('id', $item1->id));
        $this->assertTrue($itemsWithAnyTags->contains('id', $item2->id));
        $this->assertFalse($itemsWithAnyTags->contains('id', $item3->id));
    }

    public function test_scope_with_all_tags_works_with_tag_models(): void
    {
        $tag1 = Tag::factory()->create();
        $tag2 = Tag::factory()->create();
        $item = Item::factory()->create();

        TagItem::factory()->create(['tag_id' => $tag1->id, 'item_id' => $item->id]);
        TagItem::factory()->create(['tag_id' => $tag2->id, 'item_id' => $item->id]);

        $itemsWithBothTags = Item::withAllTags([$tag1, $tag2])->get();

        $this->assertCount(1, $itemsWithBothTags);
        $this->assertTrue($itemsWithBothTags->contains('id', $item->id));
    }

    public function test_scope_with_any_tags_works_with_tag_models(): void
    {
        $tag1 = Tag::factory()->create();
        $tag2 = Tag::factory()->create();
        $item = Item::factory()->create();

        TagItem::factory()->create(['tag_id' => $tag1->id, 'item_id' => $item->id]);

        $itemsWithAnyTags = Item::withAnyTags([$tag1, $tag2])->get();

        $this->assertCount(1, $itemsWithAnyTags);
        $this->assertTrue($itemsWithAnyTags->contains('id', $item->id));
    }
}
