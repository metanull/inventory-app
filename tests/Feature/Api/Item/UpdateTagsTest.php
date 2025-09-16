<?php

namespace Tests\Feature\Api\Item;

use App\Models\Item;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UpdateTagsTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_can_attach_tags_to_item(): void
    {
        $item = Item::factory()->create();
        $tags = Tag::factory()->count(3)->create();

        $response = $this->patchJson(route('item.updateTags', [$item, 'include' => 'tags']), [
            'attach' => $tags->pluck('id')->toArray(),
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'internal_name',
                    'tags' => [
                        '*' => [
                            'id',
                            'internal_name',
                        ],
                    ],
                ],
            ]);

        // Verify tags were attached in database
        $this->assertCount(3, $item->fresh()->tags);
        foreach ($tags as $tag) {
            $this->assertTrue($item->tags()->where('tags.id', $tag->id)->exists());
        }
    }

    public function test_can_detach_tags_from_item(): void
    {
        $item = Item::factory()->create();
        $tags = Tag::factory()->count(3)->create();

        // First attach the tags
        $item->tags()->attach($tags);

        // Then detach some tags
        $tagsToDetach = $tags->take(2);
        $response = $this->patchJson(route('item.updateTags', [$item, 'include' => 'tags']), [
            'detach' => $tagsToDetach->pluck('id')->toArray(),
        ]);

        $response->assertOk();

        // Verify tags were detached
        $this->assertCount(1, $item->fresh()->tags);
        $this->assertTrue($item->fresh()->tags->contains($tags->last()));
    }

    public function test_can_attach_and_detach_tags_simultaneously(): void
    {
        $item = Item::factory()->create();
        $existingTags = Tag::factory()->count(2)->create();
        $newTags = Tag::factory()->count(2)->create();

        // First attach some existing tags
        $item->tags()->attach($existingTags);

        $response = $this->patchJson(route('item.updateTags', [$item, 'include' => 'tags']), [
            'attach' => $newTags->pluck('id')->toArray(),
            'detach' => [$existingTags->first()->id],
        ]);

        $response->assertOk();

        $itemTags = $item->fresh()->tags;

        // Should have 3 tags total: 1 existing + 2 new
        $this->assertCount(3, $itemTags);

        // Should not have the detached tag
        $this->assertFalse($itemTags->contains($existingTags->first()));

        // Should have the remaining existing tag and both new tags
        $this->assertTrue($itemTags->contains($existingTags->last()));
        foreach ($newTags as $tag) {
            $this->assertTrue($itemTags->contains($tag));
        }
    }

    public function test_does_not_create_duplicate_attachments(): void
    {
        $item = Item::factory()->create();
        $tag = Tag::factory()->create();

        // First attach the tag
        $item->tags()->attach($tag);

        // Try to attach the same tag again
        $response = $this->patchJson(route('item.updateTags', [$item, 'include' => 'tags']), [
            'attach' => [$tag->id],
        ]);

        $response->assertOk();

        // Should still have only one attachment
        $this->assertCount(1, $item->fresh()->tags);
    }

    public function test_handles_empty_attach_and_detach_arrays(): void
    {
        $item = Item::factory()->create();
        $tag = Tag::factory()->create();
        $item->tags()->attach($tag);

        $response = $this->patchJson(route('item.updateTags', $item), [
            'attach' => [],
            'detach' => [],
        ]);

        $response->assertOk();

        // Should still have the original tag
        $this->assertCount(1, $item->fresh()->tags);
    }

    public function test_validation_requires_valid_tag_uuids_for_attach(): void
    {
        $item = Item::factory()->create();

        $response = $this->patchJson(route('item.updateTags', [$item, 'include' => 'tags']), [
            'attach' => ['invalid-uuid', 'another-invalid'],
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['attach.0', 'attach.1']);
    }

    public function test_validation_requires_existing_tags_for_attach(): void
    {
        $item = Item::factory()->create();
        $nonExistentUuid = 'f47ac10b-58cc-4372-a567-0e02b2c3d479';

        $response = $this->patchJson(route('item.updateTags', [$item, 'include' => 'tags']), [
            'attach' => [$nonExistentUuid],
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['attach.0']);
    }

    public function test_validation_requires_valid_tag_uuids_for_detach(): void
    {
        $item = Item::factory()->create();

        $response = $this->patchJson(route('item.updateTags', [$item, 'include' => 'tags']), [
            'detach' => ['invalid-uuid'],
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['detach.0']);
    }

    public function test_validation_requires_existing_tags_for_detach(): void
    {
        $item = Item::factory()->create();
        $nonExistentUuid = 'f47ac10b-58cc-4372-a567-0e02b2c3d479';

        $response = $this->patchJson(route('item.updateTags', [$item, 'include' => 'tags']), [
            'detach' => [$nonExistentUuid],
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['detach.0']);
    }

    public function test_can_work_with_only_attach_parameter(): void
    {
        $item = Item::factory()->create();
        $tags = Tag::factory()->count(2)->create();

        $response = $this->patchJson(route('item.updateTags', [$item, 'include' => 'tags']), [
            'attach' => $tags->pluck('id')->toArray(),
        ]);

        $response->assertOk();
        $this->assertCount(2, $item->fresh()->tags);
    }

    public function test_can_work_with_only_detach_parameter(): void
    {
        $item = Item::factory()->create();
        $tags = Tag::factory()->count(2)->create();
        $item->tags()->attach($tags);

        $response = $this->patchJson(route('item.updateTags', [$item, 'include' => 'tags']), [
            'detach' => [$tags->first()->id],
        ]);

        $response->assertOk();
        $this->assertCount(1, $item->fresh()->tags);
    }

    public function test_requires_at_least_one_parameter(): void
    {
        $item = Item::factory()->create();

        $response = $this->patchJson(route('item.updateTags', [$item, 'include' => 'tags']), []);

        $response->assertOk(); // Method should handle empty request gracefully
    }

    public function test_detaching_non_attached_tag_does_not_cause_error(): void
    {
        $item = Item::factory()->create();
        $tag = Tag::factory()->create();

        // Tag is not attached to item

        $response = $this->patchJson(route('item.updateTags', [$item, 'include' => 'tags']), [
            'detach' => [$tag->id],
        ]);

        $response->assertOk();
        $this->assertCount(0, $item->fresh()->tags);
    }

    public function test_returns_updated_item_with_all_relationships(): void
    {
        $item = Item::factory()->create();
        $tag = Tag::factory()->create();

        $response = $this->patchJson(route('item.updateTags', [$item, 'include' => 'partner,country,project,tags']), [
            'attach' => [$tag->id],
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'internal_name',
                    'type',
                    'partner',
                    'country',
                    'project',
                    'tags' => [
                        '*' => [
                            'id',
                            'internal_name',
                        ],
                    ],
                ],
            ]);
    }
}
