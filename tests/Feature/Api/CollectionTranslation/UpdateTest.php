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

    public function test_can_update_collection_translation(): void
    {
        $translation = CollectionTranslation::factory()->create();
        $data = [
            'title' => $this->faker->words(3, true),
            'description' => $this->faker->paragraph,
            'url' => 'https://example.com/updated',
        ];

        $response = $this->putJson(route('collection-translation.update', ['collection_translation' => $translation->id]), $data);

        $response->assertOk()
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
            'id' => $translation->id,
            'title' => $data['title'],
            'description' => $data['description'],
            'url' => $data['url'],
        ]);
    }

    public function test_can_update_only_title(): void
    {
        $translation = CollectionTranslation::factory()->create();
        $originalDescription = $translation->description;
        $data = [
            'title' => $this->faker->words(3, true),
        ];

        $response = $this->putJson(route('collection-translation.update', ['collection_translation' => $translation->id]), $data);

        $response->assertOk();
        $this->assertDatabaseHas('collection_translations', [
            'id' => $translation->id,
            'title' => $data['title'],
            'description' => $originalDescription, // Should remain unchanged
        ]);
    }

    public function test_can_clear_nullable_fields(): void
    {
        $translation = CollectionTranslation::factory()->create([
            'description' => 'Some description',
            'url' => 'https://example.com',
        ]);

        $data = [
            'description' => null,
            'url' => null,
        ];

        $response = $this->putJson(route('collection-translation.update', ['collection_translation' => $translation->id]), $data);

        $response->assertOk();
        $this->assertDatabaseHas('collection_translations', [
            'id' => $translation->id,
            'description' => null,
            'url' => null,
        ]);
    }

    public function test_update_validates_unique_constraint_when_changing_unique_fields(): void
    {
        $collection = Collection::factory()->create();
        $language = Language::factory()->create();
        $context = Context::factory()->create();

        // Create two translations
        $translation1 = CollectionTranslation::factory()->create([
            'collection_id' => $collection->id,
            'language_id' => $language->id,
            'context_id' => $context->id,
        ]);

        $translation2 = CollectionTranslation::factory()->create();

        // Try to update translation2 to have same unique constraint as translation1
        $data = [
            'collection_id' => $collection->id,
            'language_id' => $language->id,
            'context_id' => $context->id,
        ];

        $response = $this->putJson(route('collection-translation.update', ['collection_translation' => $translation2->id]), $data);

        $response->assertUnprocessable();
    }

    public function test_update_allows_same_unique_constraint_for_same_record(): void
    {
        $translation = CollectionTranslation::factory()->create();

        // Update with same unique constraint values should be allowed
        $data = [
            'collection_id' => $translation->collection_id,
            'language_id' => $translation->language_id,
            'context_id' => $translation->context_id,
            'title' => $this->faker->words(3, true),
        ];

        $response = $this->putJson(route('collection-translation.update', ['collection_translation' => $translation->id]), $data);

        $response->assertOk();
        $this->assertDatabaseHas('collection_translations', [
            'id' => $translation->id,
            'title' => $data['title'],
        ]);
    }

    public function test_update_validates_url_format(): void
    {
        $translation = CollectionTranslation::factory()->create();
        $data = [
            'url' => 'not-a-valid-url',
        ];

        $response = $this->putJson(route('collection-translation.update', ['collection_translation' => $translation->id]), $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['url']);
    }

    public function test_update_validates_foreign_key_constraints(): void
    {
        $translation = CollectionTranslation::factory()->create();

        // Test invalid collection_id
        $response = $this->putJson(route('collection-translation.update', ['collection_translation' => $translation->id]), [
            'collection_id' => 'invalid-uuid',
        ]);
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['collection_id']);

        // Test invalid language_id
        $response = $this->putJson(route('collection-translation.update', ['collection_translation' => $translation->id]), [
            'language_id' => 'invalid',
        ]);
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['language_id']);

        // Test invalid context_id
        $response = $this->putJson(route('collection-translation.update', ['collection_translation' => $translation->id]), [
            'context_id' => 'invalid-uuid',
        ]);
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['context_id']);
    }

    public function test_update_returns_not_found_for_non_existent_translation(): void
    {
        $data = [
            'title' => $this->faker->words(3, true),
        ];

        $response = $this->putJson(route('collection-translation.update', ['collection_translation' => 'non-existent-id']), $data);

        $response->assertNotFound();
    }

    public function test_can_update_via_patch(): void
    {
        $translation = CollectionTranslation::factory()->create();
        $data = [
            'title' => $this->faker->words(3, true),
        ];

        $response = $this->patchJson(route('collection-translation.update', ['collection_translation' => $translation->id]), $data);

        $response->assertOk();
        $this->assertDatabaseHas('collection_translations', [
            'id' => $translation->id,
            'title' => $data['title'],
        ]);
    }
}
