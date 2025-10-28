<?php

namespace Tests\Feature\Api\Collection;

use App\Enums\Permission;
use App\Models\Collection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class IndexTest extends TestCase
{
    use CreatesUsersWithPermissions, RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createUserWith([Permission::VIEW_DATA->value]);
        $this->actingAs($this->user);
    }

    /**
     * Test that authenticated users can access collection index.
     */
    public function test_authenticated_user_can_access_collection_index(): void
    {
        Collection::factory()->count(3)->create();

        $response = $this->getJson(route('collection.index'));

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                '*' => [
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
            ],
        ]);
    }

    /**
     * Test that collection index returns correct number of collections.
     */
    public function test_collection_index_returns_correct_count(): void
    {
        $collections = Collection::factory()->count(5)->create();

        $response = $this->getJson(route('collection.index'));

        $response->assertOk();
        $response->assertJsonCount(5, 'data');

        foreach ($collections as $collection) {
            $response->assertJsonPath('data.*.id', fn ($ids) => in_array($collection->id, $ids));
        }
    }

    /**
     * Test that collection index includes relationships.
     */
    public function test_collection_index_includes_relationships(): void
    {
        $collection = Collection::factory()
            ->hasTranslations(2)
            ->hasPartners(2)
            ->hasItems(3)
            ->create();

        $response = $this->getJson(route('collection.index'));

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                '*' => [
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
            ],
        ]);
    }

    /**
     * Test that collection index returns empty array when no collections exist.
     */
    public function test_collection_index_returns_empty_when_no_collections(): void
    {
        $response = $this->getJson(route('collection.index'));

        $response->assertOk();
        $response->assertJsonCount(0, 'data');
    }

    /**
     * Test that collection index includes computed attributes.
     */
    public function test_collection_index_includes_computed_attributes(): void
    {
        $collection = Collection::factory()
            ->hasTranslations(2)
            ->hasPartners(3)
            ->hasItems(5)
            ->create();

        $response = $this->getJson(route('collection.index'));

        $response->assertOk();
        $response->assertJsonPath('data.0.items_count', 5);
        $response->assertJsonPath('data.0.partners_count', 3);
        $response->assertJsonPath('data.0.translations_count', 2);
    }
}
