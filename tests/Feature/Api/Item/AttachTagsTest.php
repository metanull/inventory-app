<?php

namespace Tests\Feature\Api\Item;

use App\Models\Item;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class AttachTagsTest extends TestCase
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

    public function test_can_attach_multiple_tags_to_item(): void
    {
        $item = Item::factory()->create();
        $tags = Tag::factory()->count(3)->create();

        $response = $this->postJson(route('item.attachTags', $item->id), [
            'tag_ids' => $tags->pluck('id')->toArray(),
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'internal_name',
                ],
            ]);

        $this->assertCount(3, $item->fresh()->tags);
        foreach ($tags as $tag) {
            $this->assertTrue($item->fresh()->tags->contains($tag));
        }
    }

    public function test_does_not_create_duplicates_when_some_tags_already_attached(): void
    {
        $item = Item::factory()->create();
        $existingTags = Tag::factory()->count(2)->create();
        $newTags = Tag::factory()->count(2)->create();

        $item->tags()->attach($existingTags);

        $allTagIds = $existingTags->merge($newTags)->pluck('id')->toArray();

        $response = $this->postJson(route('item.attachTags', $item->id), [
            'tag_ids' => $allTagIds,
        ]);

        $response->assertOk();

        $this->assertCount(4, $item->fresh()->tags);
    }

    public function test_validation_requires_tag_ids(): void
    {
        $item = Item::factory()->create();

        $response = $this->postJson(route('item.attachTags', $item->id), []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['tag_ids']);
    }

    public function test_validation_requires_tag_ids_to_be_array(): void
    {
        $item = Item::factory()->create();

        $response = $this->postJson(route('item.attachTags', $item->id), [
            'tag_ids' => 'not-an-array',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['tag_ids']);
    }

    public function test_validation_requires_at_least_one_tag_id(): void
    {
        $item = Item::factory()->create();

        $response = $this->postJson(route('item.attachTags', $item->id), [
            'tag_ids' => [],
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['tag_ids']);
    }

    public function test_validation_requires_valid_uuids(): void
    {
        $item = Item::factory()->create();

        $response = $this->postJson(route('item.attachTags', $item->id), [
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

        $response = $this->postJson(route('item.attachTags', $item->id), [
            'tag_ids' => [$nonExistentUuid1, $nonExistentUuid2],
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['tag_ids.0', 'tag_ids.1']);
    }

    public function test_can_attach_tags_with_include_parameter(): void
    {
        $item = Item::factory()->create();
        $tags = Tag::factory()->count(2)->create();

        $response = $this->postJson(route('item.attachTags', [$item->id, 'include' => 'tags']), [
            'tag_ids' => $tags->pluck('id')->toArray(),
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
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
