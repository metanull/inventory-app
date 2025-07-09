<?php

namespace Tests\Feature\Api\Collection;

use App\Models\Collection;
use App\Models\Context;
use App\Models\Language;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class StoreTest extends TestCase
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
     * Test that authenticated users can create collections.
     */
    public function test_authenticated_user_can_create_collection(): void
    {
        $language = Language::factory()->create();
        $context = Context::factory()->create();

        $collectionData = [
            'internal_name' => 'test_collection',
            'language_id' => $language->id,
            'context_id' => $context->id,
        ];

        $response = $this->postJson(route('collection.store'), $collectionData);

        $response->assertCreated();
        $response->assertJsonStructure([
            'data' => [
                'id',
                'internal_name',
                'language_id',
                'context_id',
                'backward_compatibility',
                'created_at',
                'updated_at',
                'language',
                'context',
                'translations',
                'partners',
                'items',
                'items_count',
                'partners_count',
                'translations_count',
            ],
        ]);

        $this->assertDatabaseHas('collections', [
            'internal_name' => 'test_collection',
            'language_id' => $language->id,
            'context_id' => $context->id,
        ]);
    }

    /**
     * Test collection creation with backward compatibility.
     */
    public function test_collection_creation_with_backward_compatibility(): void
    {
        $language = Language::factory()->create();
        $context = Context::factory()->create();

        $collectionData = [
            'internal_name' => 'test_collection_bc',
            'language_id' => $language->id,
            'context_id' => $context->id,
            'backward_compatibility' => 'legacy-id-123',
        ];

        $response = $this->postJson(route('collection.store'), $collectionData);

        $response->assertCreated();
        $response->assertJsonPath('data.backward_compatibility', 'legacy-id-123');

        $this->assertDatabaseHas('collections', [
            'internal_name' => 'test_collection_bc',
            'backward_compatibility' => 'legacy-id-123',
        ]);
    }

    /**
     * Test collection creation requires internal_name.
     */
    public function test_collection_creation_requires_internal_name(): void
    {
        $language = Language::factory()->create();
        $context = Context::factory()->create();

        $collectionData = [
            'language_id' => $language->id,
            'context_id' => $context->id,
        ];

        $response = $this->postJson(route('collection.store'), $collectionData);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['internal_name']);
    }

    /**
     * Test collection creation requires language_id.
     */
    public function test_collection_creation_requires_language_id(): void
    {
        $context = Context::factory()->create();

        $collectionData = [
            'internal_name' => 'test_collection',
            'context_id' => $context->id,
        ];

        $response = $this->postJson(route('collection.store'), $collectionData);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['language_id']);
    }

    /**
     * Test collection creation requires context_id.
     */
    public function test_collection_creation_requires_context_id(): void
    {
        $language = Language::factory()->create();

        $collectionData = [
            'internal_name' => 'test_collection',
            'language_id' => $language->id,
        ];

        $response = $this->postJson(route('collection.store'), $collectionData);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['context_id']);
    }

    /**
     * Test collection creation requires valid language_id.
     */
    public function test_collection_creation_requires_valid_language_id(): void
    {
        $context = Context::factory()->create();

        $collectionData = [
            'internal_name' => 'test_collection',
            'language_id' => 'invalid',
            'context_id' => $context->id,
        ];

        $response = $this->postJson(route('collection.store'), $collectionData);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['language_id']);
    }

    /**
     * Test collection creation requires valid context_id.
     */
    public function test_collection_creation_requires_valid_context_id(): void
    {
        $language = Language::factory()->create();

        $collectionData = [
            'internal_name' => 'test_collection',
            'language_id' => $language->id,
            'context_id' => $this->faker->uuid(),
        ];

        $response = $this->postJson(route('collection.store'), $collectionData);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['context_id']);
    }

    /**
     * Test collection creation requires unique internal_name.
     */
    public function test_collection_creation_requires_unique_internal_name(): void
    {
        $existingCollection = Collection::factory()->create();
        $language = Language::factory()->create();
        $context = Context::factory()->create();

        $collectionData = [
            'internal_name' => $existingCollection->internal_name,
            'language_id' => $language->id,
            'context_id' => $context->id,
        ];

        $response = $this->postJson(route('collection.store'), $collectionData);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['internal_name']);
    }

    /**
     * Test collection creation with empty strings.
     */
    public function test_collection_creation_with_empty_strings(): void
    {
        $collectionData = [
            'internal_name' => '',
            'language_id' => '',
            'context_id' => '',
        ];

        $response = $this->postJson(route('collection.store'), $collectionData);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['internal_name', 'language_id', 'context_id']);
    }

    /**
     * Test collection creation with too long internal_name.
     */
    public function test_collection_creation_with_too_long_internal_name(): void
    {
        $language = Language::factory()->create();
        $context = Context::factory()->create();

        $collectionData = [
            'internal_name' => str_repeat('a', 256), // Longer than 255 chars
            'language_id' => $language->id,
            'context_id' => $context->id,
        ];

        $response = $this->postJson(route('collection.store'), $collectionData);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['internal_name']);
    }
}
