<?php

namespace Tests\Feature\Api\ItemTranslation;

use App\Models\Author;
use App\Models\Context;
use App\Models\Item;
use App\Models\ItemTranslation;
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

    public function test_can_store_item_translation(): void
    {
        $item = Item::factory()->withoutTranslations()->create();
        $language = Language::factory()->create();
        $context = Context::factory()->create();

        $data = ItemTranslation::factory()->make([
            'item_id' => $item->id,
            'language_id' => $language->id,
            'context_id' => $context->id,
        ])->toArray();

        $response = $this->postJson(route('item-translation.store'), $data);

        $response->assertCreated()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'item_id',
                    'language_id',
                    'context_id',
                    'name',
                    'description',
                    'created_at',
                    'updated_at',
                ],
            ]);

        $this->assertDatabaseHas('item_translations', [
            'item_id' => $data['item_id'],
            'language_id' => $data['language_id'],
            'context_id' => $data['context_id'],
            'name' => $data['name'],
            'description' => $data['description'],
        ]);
    }

    public function test_store_requires_item_id(): void
    {
        $language = Language::factory()->create();
        $context = Context::factory()->create();
        $data = ItemTranslation::factory()->make([
            'language_id' => $language->id,
            'context_id' => $context->id,
        ])->except(['item_id']);

        $response = $this->postJson(route('item-translation.store'), $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['item_id']);
    }

    public function test_store_requires_language_id(): void
    {
        $item = Item::factory()->withoutTranslations()->create();
        $context = Context::factory()->create();
        $data = ItemTranslation::factory()->make([
            'item_id' => $item->id,
            'context_id' => $context->id,
        ])->except(['language_id']);

        $response = $this->postJson(route('item-translation.store'), $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['language_id']);
    }

    public function test_store_requires_context_id(): void
    {
        $item = Item::factory()->withoutTranslations()->create();
        $language = Language::factory()->create();
        $data = ItemTranslation::factory()->make([
            'item_id' => $item->id,
            'language_id' => $language->id,
        ])->except(['context_id']);

        $response = $this->postJson(route('item-translation.store'), $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['context_id']);
    }

    public function test_store_requires_name(): void
    {
        $item = Item::factory()->withoutTranslations()->create();
        $language = Language::factory()->create();
        $context = Context::factory()->create();
        $data = ItemTranslation::factory()->make([
            'item_id' => $item->id,
            'language_id' => $language->id,
            'context_id' => $context->id,
        ])->except(['name']);

        $response = $this->postJson(route('item-translation.store'), $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    public function test_store_requires_description(): void
    {
        $item = Item::factory()->withoutTranslations()->create();
        $language = Language::factory()->create();
        $context = Context::factory()->create();
        $data = ItemTranslation::factory()->make([
            'item_id' => $item->id,
            'language_id' => $language->id,
            'context_id' => $context->id,
        ])->except(['description']);

        $response = $this->postJson(route('item-translation.store'), $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['description']);
    }

    public function test_store_validates_unique_constraint(): void
    {
        $item = Item::factory()->withoutTranslations()->create();
        $language = Language::factory()->create();
        $context = Context::factory()->create();

        // Create first translation
        ItemTranslation::factory()->create([
            'item_id' => $item->id,
            'language_id' => $language->id,
            'context_id' => $context->id,
        ]);

        // Try to create duplicate
        $data = ItemTranslation::factory()->make([
            'item_id' => $item->id,
            'language_id' => $language->id,
            'context_id' => $context->id,
        ])->toArray();

        $response = $this->postJson(route('item-translation.store'), $data);

        $response->assertUnprocessable();
    }

    public function test_store_allows_nullable_fields(): void
    {
        $item = Item::factory()->withoutTranslations()->create();
        $language = Language::factory()->create();
        $context = Context::factory()->create();

        $data = [
            'item_id' => $item->id,
            'language_id' => $language->id,
            'context_id' => $context->id,
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->paragraph,
            'alternate_name' => null,
            'type' => null,
            'holder' => null,
            'owner' => null,
            'initial_owner' => null,
            'dates' => null,
            'location' => null,
            'dimensions' => null,
            'place_of_production' => null,
            'method_for_datation' => null,
            'method_for_provenance' => null,
            'obtention' => null,
            'bibliography' => null,
            'author_id' => null,
            'text_copy_editor_id' => null,
            'translator_id' => null,
            'translation_copy_editor_id' => null,
            'backward_compatibility' => null,
            'extra' => null,
        ];

        $response = $this->postJson(route('item-translation.store'), $data);

        $response->assertCreated();
        $this->assertDatabaseHas('item_translations', [
            'item_id' => $data['item_id'],
            'language_id' => $data['language_id'],
            'context_id' => $data['context_id'],
            'name' => $data['name'],
            'description' => $data['description'],
            'alternate_name' => null,
        ]);
    }

    public function test_store_with_author_relationships(): void
    {
        $item = Item::factory()->withoutTranslations()->create();
        $language = Language::factory()->create();
        $context = Context::factory()->create();
        $author = Author::factory()->create();
        $textCopyEditor = Author::factory()->create();
        $translator = Author::factory()->create();
        $translationCopyEditor = Author::factory()->create();

        $data = ItemTranslation::factory()->make([
            'item_id' => $item->id,
            'language_id' => $language->id,
            'context_id' => $context->id,
            'author_id' => $author->id,
            'text_copy_editor_id' => $textCopyEditor->id,
            'translator_id' => $translator->id,
            'translation_copy_editor_id' => $translationCopyEditor->id,
        ])->toArray();

        $response = $this->postJson(route('item-translation.store'), $data);

        $response->assertCreated();
        $this->assertDatabaseHas('item_translations', [
            'item_id' => $data['item_id'],
            'language_id' => $data['language_id'],
            'context_id' => $data['context_id'],
            'author_id' => $author->id,
            'text_copy_editor_id' => $textCopyEditor->id,
            'translator_id' => $translator->id,
            'translation_copy_editor_id' => $translationCopyEditor->id,
        ]);
    }

    public function test_store_with_extra_json_data(): void
    {
        $item = Item::factory()->withoutTranslations()->create();
        $language = Language::factory()->create();
        $context = Context::factory()->create();
        $extraData = ['notes' => 'Test note', 'metadata' => ['key' => 'value']];

        $data = ItemTranslation::factory()->make([
            'item_id' => $item->id,
            'language_id' => $language->id,
            'context_id' => $context->id,
            'extra' => $extraData,
        ])->toArray();

        $response = $this->postJson(route('item-translation.store'), $data);

        $response->assertCreated();

        $translation = ItemTranslation::where('item_id', $item->id)
            ->where('language_id', $language->id)
            ->where('context_id', $context->id)
            ->first();

        $this->assertEquals($extraData, $translation->extra);
    }
}
