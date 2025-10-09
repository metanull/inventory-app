<?php

namespace Tests\Feature\Api\Tag;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class StoreTest extends TestCase
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
     * Authentication: store allows authenticated users.
     */
    public function test_store_allows_authenticated_users()
    {
        $data = Tag::factory()->make()->except(['id']);

        $response = $this->postJson(route('tag.store'), $data);
        $response->assertCreated();
    }

    /**
     * Structure: store returns expected JSON structure.
     */
    public function test_store_returns_expected_structure()
    {
        $data = Tag::factory()->make()->except(['id']);

        $response = $this->postJson(route('tag.store'), $data);

        $response->assertCreated();
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
     * Content: store creates tag with provided data.
     */
    public function test_store_creates_tag_with_provided_data()
    {
        $data = Tag::factory()->make()->except(['id']);

        $response = $this->postJson(route('tag.store'), $data);

        $response->assertCreated();
        $response->assertJsonPath('data.internal_name', $data['internal_name']);
        $response->assertJsonPath('data.backward_compatibility', $data['backward_compatibility']);
        $response->assertJsonPath('data.description', $data['description']);

        $this->assertDatabaseHas('tags', [
            'internal_name' => $data['internal_name'],
            'backward_compatibility' => $data['backward_compatibility'],
            'description' => $data['description'],
        ]);
    }

    /**
     * Validation: store requires internal_name.
     */
    public function test_store_requires_internal_name()
    {
        $data = Tag::factory()->make()->except(['id', 'internal_name']);

        $response = $this->postJson(route('tag.store'), $data);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['internal_name']);
    }

    /**
     * Validation: store requires description.
     */
    public function test_store_requires_description()
    {
        $data = Tag::factory()->make()->except(['id', 'description']);

        $response = $this->postJson(route('tag.store'), $data);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['description']);
    }

    /**
     * Validation: store accepts nullable backward_compatibility.
     */
    public function test_store_accepts_nullable_backward_compatibility()
    {
        $data = Tag::factory()->make([
            'backward_compatibility' => null,
        ])->except(['id']);

        $response = $this->postJson(route('tag.store'), $data);

        $response->assertCreated();
        $response->assertJsonPath('data.backward_compatibility', null);
    }

    /**
     * Validation: store prohibits id field.
     */
    public function test_store_prohibits_id_field()
    {
        $data = Tag::factory()->make()->toArray();

        $response = $this->postJson(route('tag.store'), $data);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['id']);
    }

    /**
     * Content: store auto-generates UUID for id.
     */
    public function test_store_auto_generates_uuid_for_id()
    {
        $data = Tag::factory()->make()->except(['id']);

        $response = $this->postJson(route('tag.store'), $data);

        $response->assertCreated();
        $response->assertJsonStructure(['data' => ['id']]);

        $id = $response->json('data.id');
        $this->assertIsString($id);
        $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $id);
    }

    /**
     * Validation: internal_name must be unique.
     */
    public function test_store_requires_unique_internal_name()
    {
        $existingTag = Tag::factory()->create();
        $data = Tag::factory()->make([
            'internal_name' => $existingTag->internal_name,
        ])->except(['id']);

        $response = $this->postJson(route('tag.store'), $data);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['internal_name']);
    }
}
