<?php

declare(strict_types=1);

namespace Tests\Feature\Web\ItemTranslation;

use App\Models\Context;
use App\Models\Item;
use App\Models\ItemTranslation;
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

    public function test_update_modifies_item_translation_and_redirects(): void
    {
        $item = Item::factory()->create();
        $language = Language::factory()->create();
        $context = Context::factory()->create();

        $translation = ItemTranslation::factory()->create([
            'item_id' => $item->id,
            'language_id' => $language->id,
            'context_id' => $context->id,
            'name' => 'Original Name',
            'description' => 'Original description',
        ]);

        $payload = [
            'item_id' => $item->id,
            'language_id' => $language->id,
            'context_id' => $context->id,
            'name' => 'Updated Name',
            'description' => 'Updated description',
        ];

        $response = $this->put(route('item-translations.update', $translation), $payload);
        $response->assertRedirect();

        $this->assertDatabaseHas('item_translations', [
            'id' => $translation->id,
            'name' => 'Updated Name',
            'description' => 'Updated description',
        ]);
    }

    public function test_update_validation_errors(): void
    {
        $item = Item::factory()->create();
        $language = Language::factory()->create();
        $context = Context::factory()->create();

        $translation = ItemTranslation::factory()->create([
            'item_id' => $item->id,
            'language_id' => $language->id,
            'context_id' => $context->id,
        ]);

        $response = $this->put(route('item-translations.update', $translation), [
            'name' => '',
            'description' => '',
        ]);

        $response->assertSessionHasErrors(['item_id', 'language_id', 'context_id', 'name', 'description']);
    }
}
