<?php

declare(strict_types=1);

namespace Tests\Feature\Web\ItemTranslation;

use App\Models\Author;
use App\Models\Context;
use App\Models\Item;
use App\Models\Language;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\RequiresDataPermissions;

class StoreTest extends TestCase
{
    use RefreshDatabase;
    use RequiresDataPermissions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actAsRegularUser();
    }

    public function test_store_persists_item_translation_and_redirects(): void
    {
        $item = Item::factory()->create();
        $language = Language::factory()->create();
        $context = Context::factory()->create();

        $payload = [
            'item_id' => $item->id,
            'language_id' => $language->id,
            'context_id' => $context->id,
            'name' => 'Test Item Translation',
            'description' => 'Test description',
        ];

        $response = $this->post(route('item-translations.store'), $payload);
        $response->assertRedirect();

        $this->assertDatabaseHas('item_translations', [
            'item_id' => $item->id,
            'language_id' => $language->id,
            'context_id' => $context->id,
            'name' => 'Test Item Translation',
            'description' => 'Test description',
        ]);
    }

    public function test_store_with_all_optional_fields(): void
    {
        $item = Item::factory()->create();
        $language = Language::factory()->create();
        $context = Context::factory()->create();
        $author = Author::factory()->create();

        $payload = [
            'item_id' => $item->id,
            'language_id' => $language->id,
            'context_id' => $context->id,
            'name' => 'Full Translation',
            'alternate_name' => 'Alternative Name',
            'description' => 'Full description',
            'type' => 'painting',
            'holder' => 'Museum A',
            'owner' => 'Owner A',
            'initial_owner' => 'Initial Owner A',
            'dates' => '1920-1930',
            'location' => 'Gallery 1',
            'dimensions' => '100x80cm',
            'place_of_production' => 'Paris',
            'method_for_datation' => 'Carbon dating',
            'method_for_provenance' => 'Documentary evidence',
            'obtention' => 'Purchased at auction',
            'bibliography' => 'Reference 1, Reference 2',
            'author_id' => $author->id,
            'text_copy_editor_id' => $author->id,
            'translator_id' => $author->id,
            'translation_copy_editor_id' => $author->id,
            'backward_compatibility' => 'legacy-001',
            'extra' => '{"custom": "data"}',
        ];

        $response = $this->post(route('item-translations.store'), $payload);
        $response->assertRedirect();

        $this->assertDatabaseHas('item_translations', [
            'name' => 'Full Translation',
            'alternate_name' => 'Alternative Name',
            'type' => 'painting',
            'holder' => 'Museum A',
            'author_id' => $author->id,
        ]);
    }

    public function test_store_validation_errors(): void
    {
        $response = $this->post(route('item-translations.store'), [
            'name' => '',
        ]);

        $response->assertSessionHasErrors(['item_id', 'language_id', 'context_id', 'name']);
    }

    public function test_store_enforces_unique_constraint(): void
    {
        $item = Item::factory()->create();
        $language = Language::factory()->create();
        $context = Context::factory()->create();

        // Create first translation
        $payload = [
            'item_id' => $item->id,
            'language_id' => $language->id,
            'context_id' => $context->id,
            'name' => 'Test Translation',
            'description' => 'Test description',
        ];

        $this->post(route('item-translations.store'), $payload);

        // Try to create duplicate
        $response = $this->post(route('item-translations.store'), $payload);
        $response->assertSessionHasErrors();
    }
}
