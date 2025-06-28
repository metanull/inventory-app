<?php

namespace Tests\Feature\Api\Tag;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DestroyTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    /**
     * Authentication: destroy allows authenticated users.
     */
    public function test_destroy_allows_authenticated_users()
    {
        $tag = Tag::factory()->create();

        $response = $this->deleteJson(route('tag.destroy', $tag));
        $response->assertNoContent();
    }

    /**
     * Content: destroy removes tag from database.
     */
    public function test_destroy_removes_tag_from_database()
    {
        $tag = Tag::factory()->create();

        $response = $this->deleteJson(route('tag.destroy', $tag));

        $response->assertNoContent();
        $this->assertDatabaseMissing('tags', [
            'id' => $tag->id,
        ]);
    }

    /**
     * Error: destroy returns 404 for non-existent tag.
     */
    public function test_destroy_returns_404_for_non_existent_tag()
    {
        $nonExistentId = $this->faker->uuid();

        $response = $this->deleteJson(route('tag.destroy', $nonExistentId));
        $response->assertNotFound();
    }

    /**
     * Content: destroy does not affect other tags.
     */
    public function test_destroy_does_not_affect_other_tags()
    {
        $tag1 = Tag::factory()->create();
        $tag2 = Tag::factory()->create();

        $response = $this->deleteJson(route('tag.destroy', $tag1));

        $response->assertNoContent();
        $this->assertDatabaseMissing('tags', [
            'id' => $tag1->id,
        ]);
        $this->assertDatabaseHas('tags', [
            'id' => $tag2->id,
        ]);
    }
}
