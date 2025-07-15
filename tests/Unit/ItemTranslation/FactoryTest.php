<?php

namespace Tests\Unit\ItemTranslation;

use App\Models\Author;
use App\Models\Context;
use App\Models\Item;
use App\Models\ItemTranslation;
use App\Models\Language;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FactoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_factory_creates_valid_item_translation(): void
    {
        $translation = ItemTranslation::factory()->create();

        $this->assertDatabaseHas('item_translations', [
            'id' => $translation->id,
            'item_id' => $translation->item_id,
            'language_id' => $translation->language_id,
            'context_id' => $translation->context_id,
            'name' => $translation->name,
        ]);

        // Test UUID format
        $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $translation->id);
        $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $translation->item_id);
        $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $translation->context_id);

        // Test language_id format (3 characters)
        $this->assertEquals(3, strlen($translation->language_id));

        // Test required fields
        $this->assertNotNull($translation->name);
        $this->assertNotNull($translation->description);

        // Test timestamps
        $this->assertNotNull($translation->created_at);
        $this->assertNotNull($translation->updated_at);
    }

    public function test_factory_creates_item_translation_with_item_relationship(): void
    {
        $translation = ItemTranslation::factory()->create();

        $item = $translation->item;
        $this->assertNotNull($item);
        $this->assertInstanceOf(Item::class, $item);
        $this->assertEquals($translation->item_id, $item->id);
    }

    public function test_factory_creates_item_translation_with_language_relationship(): void
    {
        $translation = ItemTranslation::factory()->create();

        $language = $translation->language;
        $this->assertNotNull($language);
        $this->assertInstanceOf(Language::class, $language);
        $this->assertEquals($translation->language_id, $language->id);
    }

    public function test_factory_creates_item_translation_with_context_relationship(): void
    {
        $translation = ItemTranslation::factory()->create();

        $context = $translation->context;
        $this->assertNotNull($context);
        $this->assertInstanceOf(Context::class, $context);
        $this->assertEquals($translation->context_id, $context->id);
    }

    public function test_factory_creates_item_translation_with_author_relationships(): void
    {
        $translation = ItemTranslation::factory()->withAllAuthors()->create();

        $this->assertNotNull($translation->author_id);
        $this->assertNotNull($translation->text_copy_editor_id);
        $this->assertNotNull($translation->translator_id);
        $this->assertNotNull($translation->translation_copy_editor_id);

        $this->assertInstanceOf(Author::class, $translation->author);
        $this->assertInstanceOf(Author::class, $translation->textCopyEditor);
        $this->assertInstanceOf(Author::class, $translation->translator);
        $this->assertInstanceOf(Author::class, $translation->translationCopyEditor);
    }

    public function test_factory_handles_nullable_fields(): void
    {
        $translation = ItemTranslation::factory()->create([
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
            'extra' => null,
            'author_id' => null,
            'text_copy_editor_id' => null,
            'translator_id' => null,
            'translation_copy_editor_id' => null,
            'backward_compatibility' => null,
        ]);

        $this->assertNull($translation->alternate_name);
        $this->assertNull($translation->type);
        $this->assertNull($translation->holder);
        $this->assertNull($translation->owner);
        $this->assertNull($translation->initial_owner);
        $this->assertNull($translation->dates);
        $this->assertNull($translation->location);
        $this->assertNull($translation->dimensions);
        $this->assertNull($translation->place_of_production);
        $this->assertNull($translation->method_for_datation);
        $this->assertNull($translation->method_for_provenance);
        $this->assertNull($translation->obtention);
        $this->assertNull($translation->bibliography);
        $this->assertNull($translation->extra);
        $this->assertNull($translation->author_id);
        $this->assertNull($translation->text_copy_editor_id);
        $this->assertNull($translation->translator_id);
        $this->assertNull($translation->translation_copy_editor_id);
        $this->assertNull($translation->backward_compatibility);
    }

    public function test_factory_handles_json_extra_field(): void
    {
        $extraData = ['custom_field' => 'custom_value', 'another_field' => 'another_value'];
        $translation = ItemTranslation::factory()->create([
            'extra' => $extraData,
        ]);

        $this->assertEquals((object) $extraData, $translation->extra);
    }

    public function test_factory_enforces_unique_constraint_on_item_language_context_combination(): void
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

        // Attempt to create duplicate should fail
        $this->expectException(QueryException::class);
        ItemTranslation::factory()->create([
            'item_id' => $item->id,
            'language_id' => $language->id,
            'context_id' => $context->id,
        ]);
    }

    public function test_factory_with_default_context_state(): void
    {
        $defaultContext = Context::factory()->default()->create();
        $translation = ItemTranslation::factory()->withDefaultContext()->create();

        $this->assertEquals($defaultContext->id, $translation->context_id);
    }

    public function test_factory_for_specific_language(): void
    {
        $language = Language::factory()->create();
        $translation = ItemTranslation::factory()->forLanguage($language->id)->create();

        $this->assertEquals($language->id, $translation->language_id);
    }

    public function test_factory_for_specific_context(): void
    {
        $context = Context::factory()->create();
        $translation = ItemTranslation::factory()->forContext($context->id)->create();

        $this->assertEquals($context->id, $translation->context_id);
    }

    public function test_factory_for_specific_item(): void
    {
        $item = Item::factory()->withoutTranslations()->create();
        $translation = ItemTranslation::factory()->forItem($item->id)->create();

        $this->assertEquals($item->id, $translation->item_id);
    }
}
