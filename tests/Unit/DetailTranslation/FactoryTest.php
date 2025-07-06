<?php

namespace Tests\Unit\DetailTranslation;

use App\Models\Author;
use App\Models\Context;
use App\Models\Detail;
use App\Models\DetailTranslation;
use App\Models\Language;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FactoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_factory_creates_valid_detail_translation(): void
    {
        $translation = DetailTranslation::factory()->create();

        $this->assertDatabaseHas('detail_translations', [
            'id' => $translation->id,
            'detail_id' => $translation->detail_id,
            'language_id' => $translation->language_id,
            'context_id' => $translation->context_id,
            'name' => $translation->name,
        ]);

        // Test UUID format
        $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $translation->id);
        $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $translation->detail_id);
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

    public function test_factory_creates_detail_translation_with_detail_relationship(): void
    {
        $translation = DetailTranslation::factory()->create();

        $detail = $translation->detail;
        $this->assertNotNull($detail);
        $this->assertInstanceOf(Detail::class, $detail);
        $this->assertEquals($translation->detail_id, $detail->id);
    }

    public function test_factory_creates_detail_translation_with_language_relationship(): void
    {
        $translation = DetailTranslation::factory()->create();

        $language = $translation->language;
        $this->assertNotNull($language);
        $this->assertInstanceOf(Language::class, $language);
        $this->assertEquals($translation->language_id, $language->id);
    }

    public function test_factory_creates_detail_translation_with_context_relationship(): void
    {
        $translation = DetailTranslation::factory()->create();

        $context = $translation->context;
        $this->assertNotNull($context);
        $this->assertInstanceOf(Context::class, $context);
        $this->assertEquals($translation->context_id, $context->id);
    }

    public function test_factory_creates_detail_translation_with_author_relationships(): void
    {
        $translation = DetailTranslation::factory()->withAllAuthors()->create();

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
        $translation = DetailTranslation::factory()->create([
            'alternate_name' => null,
            'extra' => null,
            'author_id' => null,
            'text_copy_editor_id' => null,
            'translator_id' => null,
            'translation_copy_editor_id' => null,
            'backward_compatibility' => null,
        ]);

        $this->assertNull($translation->alternate_name);
        $this->assertNull($translation->extra);
        $this->assertNull($translation->author_id);
        $this->assertNull($translation->text_copy_editor_id);
        $this->assertNull($translation->translator_id);
        $this->assertNull($translation->translation_copy_editor_id);
        $this->assertNull($translation->backward_compatibility);
    }

    public function test_factory_handles_json_extra_field(): void
    {
        $extraData = ['notes' => 'Test note', 'additional_info' => 'Test info'];
        $translation = DetailTranslation::factory()->create([
            'extra' => $extraData,
        ]);

        $this->assertEquals($extraData, $translation->extra);
    }

    public function test_factory_enforces_unique_constraint_on_detail_language_context_combination(): void
    {
        $detail = Detail::factory()->withoutTranslations()->create();
        $language = Language::factory()->create();
        $context = Context::factory()->create();

        // Create first translation
        DetailTranslation::factory()->create([
            'detail_id' => $detail->id,
            'language_id' => $language->id,
            'context_id' => $context->id,
        ]);

        // Attempt to create duplicate should fail
        $this->expectException(QueryException::class);
        DetailTranslation::factory()->create([
            'detail_id' => $detail->id,
            'language_id' => $language->id,
            'context_id' => $context->id,
        ]);
    }

    public function test_factory_with_default_context_state(): void
    {
        $defaultContext = Context::factory()->default()->create();
        $translation = DetailTranslation::factory()->withDefaultContext()->create();

        $this->assertEquals($defaultContext->id, $translation->context_id);
    }

    public function test_factory_for_specific_language(): void
    {
        $language = Language::factory()->create();
        $translation = DetailTranslation::factory()->forLanguage($language->id)->create();

        $this->assertEquals($language->id, $translation->language_id);
    }

    public function test_factory_for_specific_context(): void
    {
        $context = Context::factory()->create();
        $translation = DetailTranslation::factory()->forContext($context->id)->create();

        $this->assertEquals($context->id, $translation->context_id);
    }

    public function test_factory_for_specific_detail(): void
    {
        $detail = Detail::factory()->withoutTranslations()->create();
        $translation = DetailTranslation::factory()->forDetail($detail->id)->create();

        $this->assertEquals($detail->id, $translation->detail_id);
    }
}
