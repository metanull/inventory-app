<?php

namespace Tests\Feature\Api\CollectionTranslation;

use App\Models\Collection;
use App\Models\CollectionTranslation;
use App\Models\Context;
use App\Models\Language;
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

    public function test_can_store_collection_translation(): void
    {
        $collection = Collection::factory()->create();
        $language = Language::factory()->create();
        $context = Context::factory()->create();

        $data = CollectionTranslation::factory()->make([
            'collection_id' => $collection->id,
            'language_id' => $language->id,
            'context_id' => $context->id,
        ])->toArray();

        // Convert extra field to JSON string for API request if it's an array
        if (isset($data['extra']) && is_array($data['extra'])) {
            $data['extra'] = json_encode($data['extra']);
        }

        $response = $this->postJson(route('collection-translation.store'), $data);

        $response->assertCreated()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'collection_id',
                    'language_id',
                    'context_id',
                    'title',
                    'description',
                    'url',
                    'created_at',
                    'updated_at',
                ],
            ]);

        $this->assertDatabaseHas('collection_translations', [
            'collection_id' => $data['collection_id'],
            'language_id' => $data['language_id'],
            'context_id' => $data['context_id'],
            'title' => $data['title'],
        ]);
    }

    public function test_store_requires_collection_id(): void
    {
        $language = Language::factory()->create();
        $context = Context::factory()->create();
        $data = CollectionTranslation::factory()->make([
            'language_id' => $language->id,
            'context_id' => $context->id,
        ])->except(['collection_id']);

        $response = $this->postJson(route('collection-translation.store'), $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['collection_id']);
    }

    public function test_store_requires_language_id(): void
    {
        $collection = Collection::factory()->create();
        $context = Context::factory()->create();
        $data = CollectionTranslation::factory()->make([
            'collection_id' => $collection->id,
            'context_id' => $context->id,
        ])->except(['language_id']);

        $response = $this->postJson(route('collection-translation.store'), $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['language_id']);
    }

    public function test_store_requires_context_id(): void
    {
        $collection = Collection::factory()->create();
        $language = Language::factory()->create();
        $data = CollectionTranslation::factory()->make([
            'collection_id' => $collection->id,
            'language_id' => $language->id,
        ])->except(['context_id']);

        $response = $this->postJson(route('collection-translation.store'), $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['context_id']);
    }

    public function test_store_requires_title(): void
    {
        $collection = Collection::factory()->create();
        $language = Language::factory()->create();
        $context = Context::factory()->create();
        $data = CollectionTranslation::factory()->make([
            'collection_id' => $collection->id,
            'language_id' => $language->id,
            'context_id' => $context->id,
        ])->except(['title']);

        $response = $this->postJson(route('collection-translation.store'), $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['title']);
    }

    public function test_store_requires_description(): void
    {
        $collection = Collection::factory()->create();
        $language = Language::factory()->create();
        $context = Context::factory()->create();
        $data = CollectionTranslation::factory()->make([
            'collection_id' => $collection->id,
            'language_id' => $language->id,
            'context_id' => $context->id,
        ])->except(['description']);

        $response = $this->postJson(route('collection-translation.store'), $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['description']);
    }

    public function test_store_validates_unique_constraint(): void
    {
        $collection = Collection::factory()->create();
        $language = Language::factory()->create();
        $context = Context::factory()->create();

        // Create first translation
        CollectionTranslation::factory()->create([
            'collection_id' => $collection->id,
            'language_id' => $language->id,
            'context_id' => $context->id,
        ]);

        // Try to create duplicate
        $data = CollectionTranslation::factory()->make([
            'collection_id' => $collection->id,
            'language_id' => $language->id,
            'context_id' => $context->id,
        ])->toArray();

        $response = $this->postJson(route('collection-translation.store'), $data);

        $response->assertUnprocessable();
    }

    public function test_store_allows_nullable_fields(): void
    {
        $collection = Collection::factory()->create();
        $language = Language::factory()->create();
        $context = Context::factory()->create();

        $data = [
            'collection_id' => $collection->id,
            'language_id' => $language->id,
            'context_id' => $context->id,
            'title' => $this->faker->words(3, true),
            'description' => $this->faker->paragraph,
            'url' => null,
            'backward_compatibility' => null,
            'extra' => null,
        ];

        $response = $this->postJson(route('collection-translation.store'), $data);

        $response->assertCreated();
        $this->assertDatabaseHas('collection_translations', [
            'collection_id' => $data['collection_id'],
            'language_id' => $data['language_id'],
            'context_id' => $data['context_id'],
            'title' => $data['title'],
            'description' => $data['description'],
            'url' => null,
        ]);
    }

    public function test_store_validates_url_format(): void
    {
        $collection = Collection::factory()->create();
        $language = Language::factory()->create();
        $context = Context::factory()->create();

        $data = CollectionTranslation::factory()->make([
            'collection_id' => $collection->id,
            'language_id' => $language->id,
            'context_id' => $context->id,
            'url' => 'not-a-valid-url',
        ])->toArray();

        $response = $this->postJson(route('collection-translation.store'), $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['url']);
    }

    public function test_store_accepts_valid_url(): void
    {
        $collection = Collection::factory()->create();
        $language = Language::factory()->create();
        $context = Context::factory()->create();

        $data = CollectionTranslation::factory()->make([
            'collection_id' => $collection->id,
            'language_id' => $language->id,
            'context_id' => $context->id,
            'url' => 'https://example.com/collection',
        ])->toArray();

        // Convert extra field to JSON string for API request if it's an array
        if (isset($data['extra']) && is_array($data['extra'])) {
            $data['extra'] = json_encode($data['extra']);
        }

        $response = $this->postJson(route('collection-translation.store'), $data);

        $response->assertCreated();
        $this->assertDatabaseHas('collection_translations', [
            'collection_id' => $data['collection_id'],
            'url' => 'https://example.com/collection',
        ]);
    }
}
