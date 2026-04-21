<?php

namespace Tests\Web\Components;

use App\Enums\ItemType;
use App\Models\Item;
use App\Models\ItemItemLink;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Web\Traits\AuthenticatesWebRequests;

/**
 * Tests for Sidebar Blade Components
 *
 * These tests verify that sidebar components render without errors.
 * We don't test framework functionality, only our custom business logic.
 */
class SidebarComponentsTest extends TestCase
{
    use AuthenticatesWebRequests;
    use RefreshDatabase;

    public function test_parent_item_card_renders_with_parent(): void
    {
        $parent = Item::factory()->create(['type' => 'object']);
        $child = Item::factory()->create(['type' => 'detail', 'parent_id' => $parent->id]);

        $view = $this->blade(
            '<x-sidebar.parent-item-card :model="$model" :parent-item="$parentItem" :parent-options="$parentOptions" />',
            [
                'model' => $child,
                'parentItem' => $parent,
                'parentOptions' => Item::query()->orderBy('internal_name')->get(),
            ]
        );

        $view->assertSee($parent->internal_name);
        $view->assertSee('Parent Item');
    }

    public function test_parent_item_card_renders_without_parent(): void
    {
        $item = Item::factory()->create(['parent_id' => null]);

        $view = $this->blade(
            '<x-sidebar.parent-item-card :model="$model" :parent-item="$parentItem" :parent-options="$parentOptions" />',
            [
                'model' => $item,
                'parentItem' => null,
                'parentOptions' => Item::query()->orderBy('internal_name')->get(),
            ]
        );

        $view->assertSee('No parent item');
    }

    public function test_children_items_card_renders_with_children(): void
    {
        $parent = Item::factory()->create(['type' => 'object']);
        $child = Item::factory()->create(['type' => 'detail', 'parent_id' => $parent->id]);

        $view = $this->blade(
            '<x-sidebar.children-items-card :model="$model" :children="$children" :child-options="$childOptions" />',
            [
                'model' => $parent,
                'children' => $parent->children()->get(),
                'childOptions' => Item::query()->whereKeyNot($parent->id)->orderBy('internal_name')->get(),
            ]
        );

        $view->assertSee($child->internal_name);
        $view->assertSee('Children');
    }

    public function test_children_items_card_renders_without_children(): void
    {
        $item = Item::factory()->create();

        $view = $this->blade(
            '<x-sidebar.children-items-card :model="$model" :children="$children" :child-options="$childOptions" />',
            [
                'model' => $item,
                'children' => collect(),
                'childOptions' => Item::query()->whereKeyNot($item->id)->orderBy('internal_name')->get(),
            ]
        );

        $view->assertSee('No children');
    }

    public function test_tags_card_renders_with_tags(): void
    {
        $item = Item::factory()->create();
        $tag = Tag::factory()->create();
        $item->tags()->attach($tag);

        $view = $this->blade(
            '<x-sidebar.tags-card :model="$model" :tags="$tags" :available-tags="$availableTags" />',
            [
                'model' => $item->fresh(),
                'tags' => $item->fresh()->tags,
                'availableTags' => Tag::query()->whereKeyNot($tag->id)->get(),
            ]
        );

        $view->assertSee($tag->description);
        $view->assertSee('Tags');
    }

    public function test_tags_card_renders_without_tags(): void
    {
        $item = Item::factory()->create();

        $view = $this->blade(
            '<x-sidebar.tags-card :model="$model" :tags="$tags" :available-tags="$availableTags" />',
            [
                'model' => $item,
                'tags' => collect(),
                'availableTags' => Tag::query()->get(),
            ]
        );

        $view->assertSee('No tags');
    }

    public function test_links_card_renders_with_links(): void
    {
        $source = Item::factory()->create();
        $target = Item::factory()->create();
        $link = ItemItemLink::factory()->between($source, $target)->create();
        $link->load(['target', 'context']);

        $view = $this->blade(
            '<x-sidebar.links-card :model="$model" :formatted-links="$formattedLinks" :link-target-options="$linkTargetOptions" :context-options="$contextOptions" />',
            [
                'model' => $source->fresh(),
                'formattedLinks' => collect([
                    (object) [
                        'id' => $link->id,
                        'item' => $link->target,
                        'direction' => 'outgoing',
                        'link' => $link,
                    ],
                ]),
                'linkTargetOptions' => Item::query()->whereKeyNot($source->id)->orderBy('internal_name')->get(),
                'contextOptions' => collect([$link->context]),
            ]
        );

        $view->assertSee('Links');
        $view->assertSee($target->internal_name);
    }

    public function test_links_card_renders_without_links(): void
    {
        $item = Item::factory()->create();

        $view = $this->blade(
            '<x-sidebar.links-card :model="$model" :formatted-links="$formattedLinks" :link-target-options="$linkTargetOptions" :context-options="$contextOptions" />',
            [
                'model' => $item,
                'formattedLinks' => collect(),
                'linkTargetOptions' => Item::query()->whereKeyNot($item->id)->orderBy('internal_name')->get(),
                'contextOptions' => collect(),
            ]
        );

        $view->assertSee('No links');
    }

    public function test_system_properties_card_renders(): void
    {
        $item = Item::factory()->create();

        $view = $this->blade(
            '<x-sidebar.system-properties-card :id="$id" :backward-compatibility-id="$bcId" :created-at="$created" :updated-at="$updated" />',
            [
                'id' => $item->id,
                'bcId' => $item->backward_compatibility,
                'created' => $item->created_at,
                'updated' => $item->updated_at,
            ]
        );

        $view->assertSee('System Info');
        $view->assertSee($item->backward_compatibility);
    }

    public function test_item_type_icon_component_renders_all_types(): void
    {
        $types = ['object', 'monument', 'detail', 'picture'];

        foreach ($types as $type) {
            $view = $this->blade(
                '<x-display.item-type-icon :type="$type" />',
                ['type' => $type]
            );

            $view->assertDontSee('Exception');
        }

        $enumView = $this->blade(
            '<x-display.item-type-icon :type="$type" />',
            ['type' => ItemType::PICTURE]
        );

        $enumView->assertDontSee('Exception');
    }
}
