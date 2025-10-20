<?php

declare(strict_types=1);

namespace Tests\Feature\Web\CollectionTranslation;

use App\Models\Collection;
use App\Models\CollectionTranslation;
use App\Models\Context;
use App\Models\Language;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\RequiresDataPermissions;

class UpdateTest extends TestCase
{
    use RefreshDatabase;
    use RequiresDataPermissions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actAsRegularUser();
    }

    public function test_update_modifies_collection_translation_and_redirects(): void
    {
        $translation = CollectionTranslation::factory()->create([
            'title' => 'Old Title',
            'description' => 'Old Description',
        ]);

        $payload = [
            'collection_id' => $translation->collection_id,
            'language_id' => $translation->language_id,
            'context_id' => $translation->context_id,
            'title' => 'Updated Title',
            'description' => 'Updated Description',
        ];

        $response = $this->put(route('collection-translations.update', $translation), $payload);
        $response->assertRedirect();

        $this->assertDatabaseHas('collection_translations', [
            'id' => $translation->id,
            'title' => 'Updated Title',
            'description' => 'Updated Description',
        ]);

        $this->assertDatabaseMissing('collection_translations', [
            'id' => $translation->id,
            'title' => 'Old Title',
        ]);
    }

    public function test_update_can_modify_url(): void
    {
        $translation = CollectionTranslation::factory()->create([
            'url' => 'https://old-url.com',
        ]);

        $payload = [
            'collection_id' => $translation->collection_id,
            'language_id' => $translation->language_id,
            'context_id' => $translation->context_id,
            'title' => $translation->title,
            'description' => $translation->description,
            'url' => 'https://new-url.com',
        ];

        $response = $this->put(route('collection-translations.update', $translation), $payload);
        $response->assertRedirect();

        $this->assertDatabaseHas('collection_translations', [
            'id' => $translation->id,
            'url' => 'https://new-url.com',
        ]);
    }

    public function test_update_validation_errors(): void
    {
        $translation = CollectionTranslation::factory()->create();

        $response = $this->put(route('collection-translations.update', $translation), [
            'title' => '',
        ]);

        $response->assertSessionHasErrors(['collection_id', 'language_id', 'context_id', 'title']);
    }

    public function test_update_enforces_unique_constraint(): void
    {
        $collection = Collection::factory()->create();
        $language = Language::factory()->create();
        $context1 = Context::factory()->create();
        $context2 = Context::factory()->create();

        // Create first translation
        $translation1 = CollectionTranslation::factory()->create([
            'collection_id' => $collection->id,
            'language_id' => $language->id,
            'context_id' => $context1->id,
        ]);

        // Create second translation with different context
        $translation2 = CollectionTranslation::factory()->create([
            'collection_id' => $collection->id,
            'language_id' => $language->id,
            'context_id' => $context2->id,
        ]);

        // Try to update translation2 to have same combination as translation1
        $payload = [
            'collection_id' => $collection->id,
            'language_id' => $language->id,
            'context_id' => $context1->id,
            'title' => 'Test Title',
            'description' => 'Test Description',
        ];

        $response = $this->put(route('collection-translations.update', $translation2), $payload);
        $response->assertSessionHasErrors();
    }
}
