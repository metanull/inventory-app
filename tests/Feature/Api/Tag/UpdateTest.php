<?php

namespace Tests\Feature\Api\Tag;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class UpdateTest extends TestCase
{
    use CreatesUsersWithPermissions, RefreshDatabase, WithFaker;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createDataUser();
        $this->actingAs($this->user);
    }

    /**
     * Authentication: update allows authenticated users.
     */
    public function test_update_allows_authenticated_users()
    {
        $tag = Tag::factory()->create();
        $data = Tag::factory()->make()->except(['id']);

        $response = $this->putJson(route('tag.update', $tag), $data);
        $response->assertOk();
    }

    /**
     * Structure: update returns expected JSON structure.
     */
    public function test_update_returns_expected_structure()
    {
        $tag = Tag::factory()->create();
        $data = Tag::factory()->make()->except(['id']);

        $response = $this->putJson(route('tag.update', $tag), $data);

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                'id',
                'internal_name',
                'backward_compatibility',
                'description',
                'created_at',
                'updated_at',
            ],
        ]);
    }

    /**
     * Content: update modifies tag with provided data.
     */
    public function test_update_modifies_tag_with_provided_data()
    {
        $tag = Tag::factory()->create();
        $data = Tag::factory()->make()->except(['id']);

        $response = $this->putJson(route('tag.update', $tag), $data);

        $response->assertOk();
        $response->assertJsonPath('data.id', $tag->id);
        $response->assertJsonPath('data.internal_name', $data['internal_name']);
        $response->assertJsonPath('data.backward_compatibility', $data['backward_compatibility']);
        $response->assertJsonPath('data.description', $data['description']);

        $this->assertDatabaseHas('tags', [
            'id' => $tag->id,
            'internal_name' => $data['internal_name'],
            'backward_compatibility' => $data['backward_compatibility'],
            'description' => $data['description'],
        ]);
    }

    /**
     * Validation: update requires internal_name.
     */
    public function test_update_requires_internal_name()
    {
        $tag = Tag::factory()->create();
        $data = Tag::factory()->make()->except(['id', 'internal_name']);

        $response = $this->putJson(route('tag.update', $tag), $data);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['internal_name']);
    }

    /**
     * Validation: update requires description.
     */
    public function test_update_requires_description()
    {
        $tag = Tag::factory()->create();
        $data = Tag::factory()->make()->except(['id', 'description']);

        $response = $this->putJson(route('tag.update', $tag), $data);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['description']);
    }

    /**
     * Validation: update accepts nullable backward_compatibility.
     */
    public function test_update_accepts_nullable_backward_compatibility()
    {
        $tag = Tag::factory()->create();
        $data = Tag::factory()->make([
            'backward_compatibility' => null,
        ])->except(['id']);

        $response = $this->putJson(route('tag.update', $tag), $data);

        $response->assertOk();
        $response->assertJsonPath('data.backward_compatibility', null);
    }

    /**
     * Validation: update prohibits id field.
     */
    public function test_update_prohibits_id_field()
    {
        $tag = Tag::factory()->create();
        $data = Tag::factory()->make()->toArray();

        $response = $this->putJson(route('tag.update', $tag), $data);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['id']);
    }

    /**
     * Error: update returns 404 for non-existent tag.
     */
    public function test_update_returns_404_for_non_existent_tag()
    {
        $nonExistentId = $this->faker->uuid();
        $data = Tag::factory()->make()->except(['id']);

        $response = $this->putJson(route('tag.update', $nonExistentId), $data);
        $response->assertNotFound();
    }

    /**
     * Validation: internal_name must be unique except for current tag.
     */
    public function test_update_requires_unique_internal_name_except_current()
    {
        $tag1 = Tag::factory()->create();
        $tag2 = Tag::factory()->create();

        $data = Tag::factory()->make([
            'internal_name' => $tag2->internal_name,
        ])->except(['id']);

        $response = $this->putJson(route('tag.update', $tag1), $data);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['internal_name']);
    }

    /**
     * Validation: tag can keep its own internal_name.
     */
    public function test_update_allows_keeping_own_internal_name()
    {
        $tag = Tag::factory()->create();
        $data = Tag::factory()->make([
            'internal_name' => $tag->internal_name,
        ])->except(['id']);

        $response = $this->putJson(route('tag.update', $tag), $data);

        $response->assertOk();
        $response->assertJsonPath('data.internal_name', $tag->internal_name);
    }
}
