<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Items;

use App\Models\Item;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ItemTagsTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->user->givePermissionTo(\App\Enums\Permission::VIEW_DATA->value);
        $this->user->givePermissionTo(\App\Enums\Permission::UPDATE_DATA->value);
        $this->actingAs($this->user);
    }

    public function test_item_show_displays_tags(): void
    {
        $item = Item::factory()->create();
        $tag = Tag::factory()->create(['internal_name' => 'artifact']);
        $item->tags()->attach($tag->id);

        $response = $this->get(route('items.show', $item));

        $response->assertOk();
        $response->assertSee('artifact');
    }

    public function test_item_show_displays_no_tags_message_when_empty(): void
    {
        $item = Item::factory()->create();

        $response = $this->get(route('items.show', $item));

        $response->assertOk();
        $response->assertSee('No tags assigned');
    }

    public function test_attach_tag_to_item(): void
    {
        $item = Item::factory()->create();
        $tag = Tag::factory()->create(['internal_name' => 'artifact']);

        $response = $this->post(route('items.tags.attach', $item), [
            'tag_id' => $tag->id,
        ]);

        $response->assertRedirect(route('items.show', $item));
        $response->assertSessionHas('success', 'Tag attached successfully');
        $this->assertTrue($item->tags()->where('tags.id', $tag->id)->exists());
    }

    public function test_attach_tag_prevents_duplicate_attachment(): void
    {
        $item = Item::factory()->create();
        $tag = Tag::factory()->create();
        $item->tags()->attach($tag->id);

        $response = $this->post(route('items.tags.attach', $item), [
            'tag_id' => $tag->id,
        ]);

        $response->assertRedirect(route('items.show', $item));
        $response->assertSessionHas('success', 'Tag is already attached to this item');
        $this->assertEquals(1, $item->tags()->count());
    }

    public function test_detach_tag_from_item(): void
    {
        $item = Item::factory()->create();
        $tag = Tag::factory()->create();
        $item->tags()->attach($tag->id);

        $response = $this->delete(route('items.tags.detach', [$item, $tag]));

        $response->assertRedirect(route('items.show', $item));
        $response->assertSessionHas('success', 'Tag removed successfully');
        $this->assertFalse($item->tags()->where('tags.id', $tag->id)->exists());
    }

    public function test_item_can_have_multiple_tags(): void
    {
        $item = Item::factory()->create();
        $tag1 = Tag::factory()->create(['internal_name' => 'artifact']);
        $tag2 = Tag::factory()->create(['internal_name' => 'ancient']);
        $tag3 = Tag::factory()->create(['internal_name' => 'pottery']);

        $item->tags()->attach([$tag1->id, $tag2->id, $tag3->id]);

        $response = $this->get(route('items.show', $item));

        $response->assertOk();
        $response->assertSee('artifact');
        $response->assertSee('ancient');
        $response->assertSee('pottery');
        $this->assertEquals(3, $item->tags()->count());
    }

    public function test_tag_list_shows_available_tags_for_attachment(): void
    {
        $item = Item::factory()->create();
        $attachedTag = Tag::factory()->create(['internal_name' => 'artifact']);
        $availableTag = Tag::factory()->create(['internal_name' => 'monument']);

        $item->tags()->attach($attachedTag->id);

        $response = $this->get(route('items.show', $item));

        $response->assertOk();
        // Should show available tags in dropdown for attachment
        $response->assertSee('monument');
    }
}
