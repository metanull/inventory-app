<?php

declare(strict_types=1);

namespace Tests\Feature\Web\CollectionTranslation;

use App\Models\Collection;
use App\Models\Context;
use App\Models\Language;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class StoreTest extends TestCase
{
    use CreatesUsersWithPermissions;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsDataUser();
    }

    public function test_store_persists_collection_translation_and_redirects(): void
    {
        $collection = Collection::factory()->create();
        $language = Language::factory()->create();
        $context = Context::factory()->create();

        $payload = [
            'collection_id' => $collection->id,
            'language_id' => $language->id,
            'context_id' => $context->id,
            'title' => 'Test Collection Translation',
            'description' => 'Test description',
        ];

        $response = $this->post(route('collection-translations.store'), $payload);
        $response->assertRedirect();

        $this->assertDatabaseHas('collection_translations', [
            'collection_id' => $collection->id,
            'language_id' => $language->id,
            'context_id' => $context->id,
            'title' => 'Test Collection Translation',
            'description' => 'Test description',
        ]);
    }

    public function test_store_with_all_optional_fields(): void
    {
        $collection = Collection::factory()->create();
        $language = Language::factory()->create();
        $context = Context::factory()->create();

        $payload = [
            'collection_id' => $collection->id,
            'language_id' => $language->id,
            'context_id' => $context->id,
            'title' => 'Full Translation',
            'description' => 'Full description',
            'url' => 'https://example.com/collection',
            'backward_compatibility' => 'legacy-001',
            'extra' => '{"custom": "data"}',
        ];

        $response = $this->post(route('collection-translations.store'), $payload);
        $response->assertRedirect();

        $this->assertDatabaseHas('collection_translations', [
            'title' => 'Full Translation',
            'url' => 'https://example.com/collection',
            'backward_compatibility' => 'legacy-001',
        ]);
    }

    public function test_store_validation_errors(): void
    {
        $response = $this->post(route('collection-translations.store'), [
            'title' => '',
        ]);

        $response->assertSessionHasErrors(['collection_id', 'language_id', 'context_id', 'title']);
    }

    public function test_store_enforces_unique_constraint(): void
    {
        $collection = Collection::factory()->create();
        $language = Language::factory()->create();
        $context = Context::factory()->create();

        // Create first translation
        $payload = [
            'collection_id' => $collection->id,
            'language_id' => $language->id,
            'context_id' => $context->id,
            'title' => 'Test Translation',
            'description' => 'Test description',
        ];

        $this->post(route('collection-translations.store'), $payload);

        // Try to create duplicate
        $response = $this->post(route('collection-translations.store'), $payload);
        $response->assertSessionHasErrors();
    }
}
