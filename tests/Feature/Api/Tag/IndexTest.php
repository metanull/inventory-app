<?php

namespace Tests\Feature\Api\Tag;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class IndexTest extends TestCase
{
    use CreatesUsersWithPermissions, RefreshDatabase;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createVisitorUser();
        $this->actingAs($this->user);
    }

    /**
     * Authentication: index allows authenticated users.
     */
    public function test_index_allows_authenticated_users()
    {
        $response = $this->get(route('tag.index'));
        $response->assertOk();
    }

    /**
     * Structure: index returns expected JSON structure.
     */
    public function test_index_returns_expected_structure()
    {
        Tag::factory()->count(3)->create();

        $response = $this->get(route('tag.index'));

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'internal_name',
                    'backward_compatibility',
                    'description',
                    'created_at',
                    'updated_at',
                ],
            ],
        ]);
    }

    /**
     * Content: index returns all tags.
     */
    public function test_index_returns_all_tags()
    {
        $tags = Tag::factory()->count(3)->create();

        $response = $this->get(route('tag.index'));

        $response->assertOk();
        $response->assertJsonCount(3, 'data');

        foreach ($tags as $tag) {
            $response->assertJsonPath('data.*.id', function ($ids) use ($tag) {
                return in_array($tag->id, $ids);
            });
        }
    }

    /**
     * Content: index returns empty array when no tags exist.
     */
    public function test_index_returns_empty_array_when_no_tags_exist()
    {
        $response = $this->get(route('tag.index'));

        $response->assertOk();
        $response->assertJsonCount(0, 'data');
    }
}
