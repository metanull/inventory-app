<?php

namespace Tests\Unit\ExhibitionTranslation;

use App\Models\Context;
use App\Models\Exhibition;
use App\Models\ExhibitionTranslation;
use App\Models\Language;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class FactoryTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_exhibition_translation_factory_creates_valid_translation(): void
    {
        $translation = ExhibitionTranslation::factory()->create();

        $this->assertDatabaseHas('exhibition_translations', [
            'id' => $translation->id,
            'exhibition_id' => $translation->exhibition_id,
            'language_id' => $translation->language_id,
            'context_id' => $translation->context_id,
        ]);

        $this->assertIsString($translation->id);
        $this->assertIsString($translation->title);
        $this->assertIsString($translation->description);
        $this->assertNotNull($translation->exhibition_id);
        $this->assertNotNull($translation->language_id);
        $this->assertNotNull($translation->context_id);
    }

    public function test_exhibition_translation_factory_creates_unique_content(): void
    {
        $translation1 = ExhibitionTranslation::factory()->create();
        $translation2 = ExhibitionTranslation::factory()->create();

        $this->assertNotEquals($translation1->title, $translation2->title);
        $this->assertNotEquals($translation1->description, $translation2->description);
    }

    public function test_exhibition_translation_factory_can_create_with_specific_exhibition(): void
    {
        $exhibition = Exhibition::factory()->create();
        $translation = ExhibitionTranslation::factory()->create(['exhibition_id' => $exhibition->id]);

        $this->assertEquals($exhibition->id, $translation->exhibition_id);
        $this->assertInstanceOf(Exhibition::class, $translation->exhibition);
    }

    public function test_exhibition_translation_factory_can_create_with_specific_language(): void
    {
        $language = Language::factory()->create();
        $translation = ExhibitionTranslation::factory()->create(['language_id' => $language->id]);

        $this->assertEquals($language->id, $translation->language_id);
        $this->assertInstanceOf(Language::class, $translation->language);
    }

    public function test_exhibition_translation_factory_can_create_with_specific_context(): void
    {
        $context = Context::factory()->create();
        $translation = ExhibitionTranslation::factory()->create(['context_id' => $context->id]);

        $this->assertEquals($context->id, $translation->context_id);
        $this->assertInstanceOf(Context::class, $translation->context);
    }

    public function test_exhibition_translation_factory_can_create_with_url(): void
    {
        $translation = ExhibitionTranslation::factory()->withUrl()->create();

        $this->assertNotNull($translation->url);
        $this->assertIsString($translation->url);
        $this->assertTrue(filter_var($translation->url, FILTER_VALIDATE_URL) !== false);
    }

    public function test_exhibition_translation_factory_can_create_without_url(): void
    {
        $translation = ExhibitionTranslation::factory()->create(['url' => null]);

        $this->assertNull($translation->url);
        $this->assertDatabaseHas('exhibition_translations', [
            'id' => $translation->id,
            'url' => null,
        ]);
    }

    public function test_exhibition_translation_factory_can_create_with_backward_compatibility(): void
    {
        $translation = ExhibitionTranslation::factory()->withBackwardCompatibility()->create();

        $this->assertNotNull($translation->backward_compatibility);
        $this->assertIsString($translation->backward_compatibility);
    }

    public function test_exhibition_translation_factory_can_create_with_extra_data(): void
    {
        $extraData = ['notes' => 'Test note', 'metadata' => ['key' => 'value']];
        $translation = ExhibitionTranslation::factory()->withExtra($extraData)->create();

        $this->assertEquals($extraData, $translation->extra);
    }

    public function test_exhibition_translation_has_exhibition_relationship(): void
    {
        $translation = ExhibitionTranslation::factory()->create();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $translation->exhibition());
        $this->assertInstanceOf(Exhibition::class, $translation->exhibition);
    }

    public function test_exhibition_translation_has_language_relationship(): void
    {
        $translation = ExhibitionTranslation::factory()->create();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $translation->language());
        $this->assertInstanceOf(Language::class, $translation->language);
    }

    public function test_exhibition_translation_has_context_relationship(): void
    {
        $translation = ExhibitionTranslation::factory()->create();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $translation->context());
        $this->assertInstanceOf(Context::class, $translation->context);
    }

    public function test_exhibition_translation_uses_uuid_primary_key(): void
    {
        $translation = ExhibitionTranslation::factory()->create();

        $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $translation->id);
    }

    public function test_exhibition_translation_has_required_fillable_fields(): void
    {
        $translation = new ExhibitionTranslation;
        $fillable = $translation->getFillable();

        $this->assertContains('exhibition_id', $fillable);
        $this->assertContains('language_id', $fillable);
        $this->assertContains('context_id', $fillable);
        $this->assertContains('title', $fillable);
        $this->assertContains('description', $fillable);
        $this->assertContains('url', $fillable);
        $this->assertContains('backward_compatibility', $fillable);
        $this->assertContains('extra', $fillable);
        $this->assertNotContains('id', $fillable);
    }

    public function test_exhibition_translation_has_proper_casts(): void
    {
        $translation = new ExhibitionTranslation;
        $casts = $translation->getCasts();

        $this->assertArrayHasKey('extra', $casts);
        $this->assertEquals('array', $casts['extra']);
    }

    public function test_exhibition_translation_unique_ids_configuration(): void
    {
        $translation = new ExhibitionTranslation;

        $this->assertEquals(['id'], $translation->uniqueIds());
    }

    public function test_exhibition_translation_scope_methods_exist(): void
    {
        $translation = new ExhibitionTranslation;

        $this->assertTrue(method_exists($translation, 'scopeDefaultContext'));
        $this->assertTrue(method_exists($translation, 'scopeForLanguage'));
        $this->assertTrue(method_exists($translation, 'scopeForContext'));
    }

    public function test_exhibition_translation_respects_unique_constraint(): void
    {
        $exhibition = Exhibition::factory()->create();
        $language = Language::factory()->create();
        $context = Context::factory()->create();

        // Create first translation
        $translation1 = ExhibitionTranslation::factory()->create([
            'exhibition_id' => $exhibition->id,
            'language_id' => $language->id,
            'context_id' => $context->id,
        ]);

        // Attempting to create a second translation with the same combination should fail
        $this->expectException(\Illuminate\Database\QueryException::class);

        ExhibitionTranslation::factory()->create([
            'exhibition_id' => $exhibition->id,
            'language_id' => $language->id,
            'context_id' => $context->id,
        ]);
    }
}
