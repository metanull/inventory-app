<?php

namespace Tests\Feature\Api\Item;

use App\Models\Item;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class DetachTagTest extends TestCase
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

    public function test_can_detach_single_tag_from_item(): void
    {
        $item = Item::factory()->create();
        $tags = Tag::factory()->count(3)->create();
        $item->tags()->attach($tags);

        $tagToDetach = $tags->first();

        $response = $this->deleteJson(route('item.detachTag', $item->id), [
            'tag_id' => $tagToDetach->id,
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'internal_name',
                ],
            ]);

        $this->assertCount(2, $item->fresh()->tags);
        $this->assertFalse($item->fresh()->tags->contains($tagToDetach));
    }

    public function test_detaching_non_attached_tag_does_not_cause_error(): void
    {
        $item = Item::factory()->create();
        $tag = Tag::factory()->create();

        $response = $this->deleteJson(route('item.detachTag', $item->id), [
            'tag_id' => $tag->id,
        ]);

        $response->assertOk();
        $this->assertCount(0, $item->fresh()->tags);
    }

    public function test_validation_requires_tag_id(): void
    {
        $item = Item::factory()->create();

        $response = $this->deleteJson(route('item.detachTag', $item->id), []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['tag_id']);
    }

    public function test_validation_requires_valid_uuid_for_tag_id(): void
    {
        $item = Item::factory()->create();

        $response = $this->deleteJson(route('item.detachTag', $item->id), [
            'tag_id' => 'invalid-uuid',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['tag_id']);
    }

    public function test_validation_requires_existing_tag(): void
    {
        $item = Item::factory()->create();
        $nonExistentUuid = 'f47ac10b-58cc-4372-a567-0e02b2c3d479';

        $response = $this->deleteJson(route('item.detachTag', $item->id), [
            'tag_id' => $nonExistentUuid,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['tag_id']);
    }

    public function test_can_detach_tag_with_include_parameter(): void
    {
        $item = Item::factory()->create();
        $tag = Tag::factory()->create();
        $item->tags()->attach($tag);

        $response = $this->deleteJson(route('item.detachTag', [$item->id, 'include' => 'tags']), [
            'tag_id' => $tag->id,
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
