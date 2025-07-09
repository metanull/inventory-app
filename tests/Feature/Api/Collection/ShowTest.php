<?php

namespace Tests\Feature\Api\Collection;

use App\Models\Collection;
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
     * Test that authenticated users can show a specific collection.
     */
    public function test_authenticated_user_can_show_collection(): void
    {
        $collection = Collection::factory()->create();

        $response = $this->getJson(route('collection.show', $collection));

        $response->assertOk();
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
    }

    /**
     * Test that collection show returns correct collection data.
     */
    public function test_collection_show_returns_correct_data(): void
    {
        $collection = Collection::factory()->create();

        $response = $this->getJson(route('collection.show', $collection));

        $response->assertOk();
        $response->assertJsonPath('data.id', $collection->id);
        $response->assertJsonPath('data.internal_name', $collection->internal_name);
        $response->assertJsonPath('data.language_id', $collection->language_id);
        $response->assertJsonPath('data.context_id', $collection->context_id);
    }

    /**
     * Test that collection show includes relationships.
     */
    public function test_collection_show_includes_relationships(): void
    {
        $collection = Collection::factory()
            ->hasTranslations(2)
            ->hasPartners(2)
            ->hasItems(3)
            ->create();

        $response = $this->getJson(route('collection.show', $collection));

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                'language' => [
                    'id',
                    'internal_name',
                    'is_default',
                ],
                'context' => [
                    'id',
                    'internal_name',
                    'is_default',
                ],
                'translations' => [
                    '*' => [
                        'id',
                        'collection_id',
                        'language_id',
                        'context_id',
                        'title',
                        'description',
                        'url',
                    ],
                ],
                'partners' => [
                    '*' => [
                        'id',
                        'internal_name',
                        'type',
                    ],
                ],
                'items' => [
                    '*' => [
                        'id',
                        'internal_name',
                        'type',
                    ],
                ],
            ],
        ]);
    }

    /**
     * Test that collection show includes computed attributes.
     */
    public function test_collection_show_includes_computed_attributes(): void
    {
        $collection = Collection::factory()
            ->hasTranslations(2)
            ->hasPartners(3)
            ->hasItems(5)
            ->create();

        $response = $this->getJson(route('collection.show', $collection));

        $response->assertOk();
        $response->assertJsonPath('data.items_count', 5);
        $response->assertJsonPath('data.partners_count', 3);
        $response->assertJsonPath('data.translations_count', 2);
    }

    /**
     * Test that collection show returns 404 for non-existent collection.
     */
    public function test_collection_show_returns_404_for_non_existent_collection(): void
    {
        $nonExistentId = $this->faker->uuid();

        $response = $this->getJson(route('collection.show', $nonExistentId));

        $response->assertNotFound();
    }

    /**
     * Test that collection show works with UUID.
     */
    public function test_collection_show_works_with_uuid(): void
    {
        $collection = Collection::factory()->create();

        // Test with UUID string
        $response = $this->getJson(route('collection.show', $collection->id));

        $response->assertOk();
        $response->assertJsonPath('data.id', $collection->id);
    }
}
