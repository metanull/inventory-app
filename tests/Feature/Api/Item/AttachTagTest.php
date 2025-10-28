<?php

namespace Tests\Feature\Api\Item;

use App\Enums\Permission;
use App\Models\Item;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class AttachTagTest extends TestCase
{
    use CreatesUsersWithPermissions, RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createUserWith(Permission::dataOperations());
        $this->actingAs($this->user);
    }

    public function test_can_attach_single_tag_to_item(): void
    {
        $item = Item::factory()->create();
        $tag = Tag::factory()->create();

        $response = $this->postJson(route('item.attachTag', $item->id), [
            'tag_id' => $tag->id,
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'internal_name',
                ],
            ]);

        $this->assertCount(1, $item->fresh()->tags);
        $this->assertTrue($item->fresh()->tags->contains($tag));
    }

    public function test_does_not_create_duplicate_when_tag_already_attached(): void
    {
        $item = Item::factory()->create();
        $tag = Tag::factory()->create();

        $item->tags()->attach($tag);

        $response = $this->postJson(route('item.attachTag', $item->id), [
            'tag_id' => $tag->id,
        ]);

        $response->assertOk();

        $this->assertCount(1, $item->fresh()->tags);
    }

    public function test_validation_requires_tag_id(): void
    {
        $item = Item::factory()->create();

        $response = $this->postJson(route('item.attachTag', $item->id), []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['tag_id']);
    }

    public function test_validation_requires_valid_uuid_for_tag_id(): void
    {
        $item = Item::factory()->create();

        $response = $this->postJson(route('item.attachTag', $item->id), [
            'tag_id' => 'invalid-uuid',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['tag_id']);
    }

    public function test_validation_requires_existing_tag(): void
    {
        $item = Item::factory()->create();
        $nonExistentUuid = 'f47ac10b-58cc-4372-a567-0e02b2c3d479';

        $response = $this->postJson(route('item.attachTag', $item->id), [
            'tag_id' => $nonExistentUuid,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['tag_id']);
    }

    public function test_can_attach_tag_with_include_parameter(): void
    {
        $item = Item::factory()->create();
        $tag = Tag::factory()->create();

        $response = $this->postJson(route('item.attachTag', [$item->id, 'include' => 'tags']), [
            'tag_id' => $tag->id,
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
