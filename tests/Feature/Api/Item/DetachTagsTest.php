<?php

namespace Tests\Feature\Api\Item;

use App\Models\Item;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class DetachTagsTest extends TestCase
{
    use CreatesUsersWithPermissions;
    use RefreshDatabase;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createDataUser();
        $this->actingAs($this->user);
    }

    public function test_can_detach_multiple_tags_from_item(): void
    {
        $item = Item::factory()->create();
        $tags = Tag::factory()->count(5)->create();
        $item->tags()->attach($tags);

        $tagsToDetach = $tags->take(3);

        $response = $this->deleteJson(route('item.detachTags', $item->id), [
            'tag_ids' => $tagsToDetach->pluck('id')->toArray(),
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'internal_name',
                ],
            ]);

        $this->assertCount(2, $item->fresh()->tags);
        foreach ($tagsToDetach as $tag) {
            $this->assertFalse($item->fresh()->tags->contains($tag));
        }
    }

    public function test_detaching_non_attached_tags_does_not_cause_error(): void
    {
        $item = Item::factory()->create();
        $attachedTags = Tag::factory()->count(2)->create();
        $nonAttachedTags = Tag::factory()->count(2)->create();

        $item->tags()->attach($attachedTags);

        $response = $this->deleteJson(route('item.detachTags', $item->id), [
            'tag_ids' => $nonAttachedTags->pluck('id')->toArray(),
        ]);

        $response->assertOk();
        $this->assertCount(2, $item->fresh()->tags);
    }

    public function test_validation_requires_tag_ids(): void
    {
        $item = Item::factory()->create();

        $response = $this->deleteJson(route('item.detachTags', $item->id), []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['tag_ids']);
    }

    public function test_validation_requires_tag_ids_to_be_array(): void
    {
        $item = Item::factory()->create();

        $response = $this->deleteJson(route('item.detachTags', $item->id), [
            'tag_ids' => 'not-an-array',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['tag_ids']);
    }

    public function test_validation_requires_at_least_one_tag_id(): void
    {
        $item = Item::factory()->create();

        $response = $this->deleteJson(route('item.detachTags', $item->id), [
            'tag_ids' => [],
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['tag_ids']);
    }

    public function test_validation_requires_valid_uuids(): void
    {
        $item = Item::factory()->create();

        $response = $this->deleteJson(route('item.detachTags', $item->id), [
            'tag_ids' => ['invalid-uuid', 'another-invalid'],
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['tag_ids.0', 'tag_ids.1']);
    }

    public function test_validation_requires_existing_tags(): void
    {
        $item = Item::factory()->create();
        $nonExistentUuid1 = 'f47ac10b-58cc-4372-a567-0e02b2c3d479';
        $nonExistentUuid2 = 'a47ac10b-58cc-4372-a567-0e02b2c3d470';

        $response = $this->deleteJson(route('item.detachTags', $item->id), [
            'tag_ids' => [$nonExistentUuid1, $nonExistentUuid2],
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['tag_ids.0', 'tag_ids.1']);
    }

    public function test_can_detach_tags_with_include_parameter(): void
    {
        $item = Item::factory()->create();
        $tags = Tag::factory()->count(3)->create();
        $item->tags()->attach($tags);

        $tagsToDetach = $tags->take(2);

        $response = $this->deleteJson(route('item.detachTags', [$item->id, 'include' => 'tags']), [
            'tag_ids' => $tagsToDetach->pluck('id')->toArray(),
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'tags',
                ],
            ]);
    }
}
