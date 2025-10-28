<?php

namespace Tests\Feature\Api\Collection;

use App\Enums\Permission;
use App\Models\Collection;
use App\Models\Language;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class UpdateTest extends TestCase
{
    use CreatesUsersWithPermissions, RefreshDatabase, WithFaker;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createUserWith(Permission::dataOperations());
        $this->actingAs($this->user);
    }

    /**
     * Test that authenticated users can update collections.
     */
    public function test_authenticated_user_can_update_collection(): void
    {
        $collection = Collection::factory()->create();
        $newLanguage = Language::factory()->create();

        $updateData = [
            'internal_name' => 'updated_collection_name',
            'language_id' => $newLanguage->id,
        ];

        $response = $this->putJson(route('collection.update', $collection), $updateData);

        $response->assertOk();
        $response->assertJsonPath('data.internal_name', 'updated_collection_name');
        $response->assertJsonPath('data.language_id', $newLanguage->id);

        $this->assertDatabaseHas('collections', [
            'id' => $collection->id,
            'internal_name' => 'updated_collection_name',
            'language_id' => $newLanguage->id,
        ]);
    }

    /**
     * Test collection update with partial data.
     */
    public function test_collection_update_with_partial_data(): void
    {
        $collection = Collection::factory()->create();
        $originalLanguageId = $collection->language_id;
        $originalContextId = $collection->context_id;

        $updateData = [
            'internal_name' => 'new_name_only',
        ];

        $response = $this->putJson(route('collection.update', $collection), $updateData);

        $response->assertOk();
        $response->assertJsonPath('data.internal_name', 'new_name_only');
        $response->assertJsonPath('data.language_id', $originalLanguageId);
        $response->assertJsonPath('data.context_id', $originalContextId);
    }

    /**
     * Test collection update with backward compatibility.
     */
    public function test_collection_update_with_backward_compatibility(): void
    {
        $collection = Collection::factory()->create(['backward_compatibility' => null]);

        $updateData = [
            'backward_compatibility' => 'new-legacy-id',
        ];

        $response = $this->putJson(route('collection.update', $collection), $updateData);

        $response->assertOk();
        $response->assertJsonPath('data.backward_compatibility', 'new-legacy-id');

        $this->assertDatabaseHas('collections', [
            'id' => $collection->id,
            'backward_compatibility' => 'new-legacy-id',
        ]);
    }

    /**
     * Test collection update requires valid language_id.
     */
    public function test_collection_update_requires_valid_language_id(): void
    {
        $collection = Collection::factory()->create();

        $updateData = [
            'language_id' => 'invalid',
        ];

        $response = $this->putJson(route('collection.update', $collection), $updateData);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['language_id']);
    }

    /**
     * Test collection update requires valid context_id.
     */
    public function test_collection_update_requires_valid_context_id(): void
    {
        $collection = Collection::factory()->create();

        $updateData = [
            'context_id' => $this->faker->uuid(),
        ];

        $response = $this->putJson(route('collection.update', $collection), $updateData);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['context_id']);
    }

    /**
     * Test collection update requires unique internal_name.
     */
    public function test_collection_update_requires_unique_internal_name(): void
    {
        $collection1 = Collection::factory()->create();
        $collection2 = Collection::factory()->create();

        $updateData = [
            'internal_name' => $collection2->internal_name,
        ];

        $response = $this->putJson(route('collection.update', $collection1), $updateData);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['internal_name']);
    }

    /**
     * Test collection update allows same internal_name for same collection.
     */
    public function test_collection_update_allows_same_internal_name_for_same_collection(): void
    {
        $collection = Collection::factory()->create();

        $updateData = [
            'internal_name' => $collection->internal_name,
        ];

        $response = $this->putJson(route('collection.update', $collection), $updateData);

        $response->assertOk();
    }

    /**
     * Test collection update with empty strings.
     */
    public function test_collection_update_with_empty_strings(): void
    {
        $collection = Collection::factory()->create();

        $updateData = [
            'internal_name' => '',
            'language_id' => '',
            'context_id' => '',
        ];

        $response = $this->putJson(route('collection.update', $collection), $updateData);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['internal_name', 'language_id', 'context_id']);
    }

    /**
     * Test collection update with too long internal_name.
     */
    public function test_collection_update_with_too_long_internal_name(): void
    {
        $collection = Collection::factory()->create();

        $updateData = [
            'internal_name' => str_repeat('a', 256), // Longer than 255 chars
        ];

        $response = $this->putJson(route('collection.update', $collection), $updateData);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['internal_name']);
    }

    /**
     * Test collection update returns 404 for non-existent collection.
     */
    public function test_collection_update_returns_404_for_non_existent_collection(): void
    {
        $nonExistentId = $this->faker->uuid();

        $updateData = [
            'internal_name' => 'new_name',
        ];

        $response = $this->putJson(route('collection.update', $nonExistentId), $updateData);

        $response->assertNotFound();
    }

    /**
     * Test collection update includes relationships in response.
     */
    public function test_collection_update_includes_relationships_in_response(): void
    {
        $collection = Collection::factory()
            ->hasTranslations(1)
            ->hasPartners(1)
            ->hasItems(1)
            ->create();

        $updateData = [
            'internal_name' => 'updated_with_relationships',
        ];

        $response = $this->putJson(route('collection.update', $collection), $updateData);

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                'id',
                'internal_name',
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
}
