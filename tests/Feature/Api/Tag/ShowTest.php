<?php

namespace Tests\Feature\Api\Tag;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ShowTest extends TestCase
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
     * Authentication: show allows authenticated users.
     */
    public function test_show_allows_authenticated_users()
    {
        $tag = Tag::factory()->create();
        
        $response = $this->get(route('tag.show', $tag));
        $response->assertOk();
    }

    /**
     * Structure: show returns expected JSON structure.
     */
    public function test_show_returns_expected_structure()
    {
        $tag = Tag::factory()->create();
        
        $response = $this->get(route('tag.show', $tag));
        
        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                'id',
                'internal_name',
                'backward_compatibility',
                'description',
                'created_at',
                'updated_at',
            ]
        ]);
    }

    /**
     * Content: show returns correct tag data.
     */
    public function test_show_returns_correct_tag_data()
    {
        $tag = Tag::factory()->create();
        
        $response = $this->get(route('tag.show', $tag));
        
        $response->assertOk();
        $response->assertJsonPath('data.id', $tag->id);
        $response->assertJsonPath('data.internal_name', $tag->internal_name);
        $response->assertJsonPath('data.backward_compatibility', $tag->backward_compatibility);
        $response->assertJsonPath('data.description', $tag->description);
        $response->assertJsonPath('data.created_at', $tag->created_at->toISOString());
        $response->assertJsonPath('data.updated_at', $tag->updated_at->toISOString());
    }

    /**
     * Error: show returns 404 for non-existent tag.
     */
    public function test_show_returns_404_for_non_existent_tag()
    {
        $nonExistentId = $this->faker->uuid();
        
        $response = $this->get(route('tag.show', $nonExistentId));
        $response->assertNotFound();
    }
}
