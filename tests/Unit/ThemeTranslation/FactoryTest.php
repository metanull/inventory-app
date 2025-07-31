<?php

namespace Tests\Unit\ThemeTranslation;

use App\Models\Context;
use App\Models\Language;
use App\Models\Theme;
use App\Models\ThemeTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class FactoryTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_theme_translation_factory_creates_valid_translation(): void
    {
        $theme = Theme::factory()->create();
        $context = Context::factory()->create();

        $translation = ThemeTranslation::factory()->create([
            'theme_id' => $theme->id,
            'context_id' => $context->id,
        ]);

        $this->assertDatabaseHas('theme_translations', [
            'id' => $translation->id,
            'theme_id' => $translation->theme_id,
            'language_id' => $translation->language_id,
            'context_id' => $translation->context_id,
        ]);

        $this->assertIsString($translation->id);
        $this->assertIsString($translation->title);
        $this->assertIsString($translation->description);
        $this->assertIsString($translation->introduction);
        $this->assertNotNull($translation->theme_id);
        $this->assertNotNull($translation->language_id);
        $this->assertNotNull($translation->context_id);
    }

    public function test_theme_translation_factory_creates_unique_content(): void
    {
        $theme1 = Theme::factory()->create();
        $theme2 = Theme::factory()->create();
        $context = Context::factory()->create();

        $translation1 = ThemeTranslation::factory()->create([
            'theme_id' => $theme1->id,
            'context_id' => $context->id,
        ]);
        $translation2 = ThemeTranslation::factory()->create([
            'theme_id' => $theme2->id,
            'context_id' => $context->id,
        ]);

        $this->assertNotEquals($translation1->title, $translation2->title);
        $this->assertNotEquals($translation1->description, $translation2->description);
        $this->assertNotEquals($translation1->introduction, $translation2->introduction);
    }

    public function test_theme_translation_factory_can_create_with_specific_theme(): void
    {
        $theme = Theme::factory()->create();
        $context = Context::factory()->create();

        $translation = ThemeTranslation::factory()->create([
            'theme_id' => $theme->id,
            'context_id' => $context->id,
        ]);

        $this->assertEquals($theme->id, $translation->theme_id);
        $this->assertInstanceOf(Theme::class, $translation->theme);
    }

    public function test_theme_translation_factory_can_create_with_specific_language(): void
    {
        $language = Language::factory()->create();
        $translation = ThemeTranslation::factory()->create(['language_id' => $language->id]);

        $this->assertEquals($language->id, $translation->language_id);
        $this->assertInstanceOf(Language::class, $translation->language);
    }

    public function test_theme_translation_factory_can_create_with_specific_context(): void
    {
        $context = Context::factory()->create();
        $translation = ThemeTranslation::factory()->create(['context_id' => $context->id]);

        $this->assertEquals($context->id, $translation->context_id);
        $this->assertInstanceOf(Context::class, $translation->context);
    }

    public function test_theme_translation_factory_can_create_with_backward_compatibility(): void
    {
        $translation = ThemeTranslation::factory()->withBackwardCompatibility()->create();

        $this->assertNotNull($translation->backward_compatibility);
        $this->assertIsString($translation->backward_compatibility);
    }

    public function test_theme_translation_factory_can_create_with_extra_data(): void
    {
        $extraData = ['custom_field' => 'custom_value', 'another_field' => 'another_value'];
        $translation = ThemeTranslation::factory()->withExtra($extraData)->create();

        $this->assertEquals((object) $extraData, $translation->extra);
    }

    public function test_theme_translation_has_theme_relationship(): void
    {
        $translation = ThemeTranslation::factory()->create();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $translation->theme());
        $this->assertInstanceOf(Theme::class, $translation->theme);
    }

    public function test_theme_translation_has_language_relationship(): void
    {
        $translation = ThemeTranslation::factory()->create();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $translation->language());
        $this->assertInstanceOf(Language::class, $translation->language);
    }

    public function test_theme_translation_has_context_relationship(): void
    {
        $translation = ThemeTranslation::factory()->create();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $translation->context());
        $this->assertInstanceOf(Context::class, $translation->context);
    }

    public function test_theme_translation_uses_uuid_primary_key(): void
    {
        $translation = ThemeTranslation::factory()->create();

        $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $translation->id);
    }

    public function test_theme_translation_has_required_fillable_fields(): void
    {
        $translation = new ThemeTranslation;
        $fillable = $translation->getFillable();

        $this->assertContains('theme_id', $fillable);
        $this->assertContains('language_id', $fillable);
        $this->assertContains('context_id', $fillable);
        $this->assertContains('title', $fillable);
        $this->assertContains('description', $fillable);
        $this->assertContains('introduction', $fillable);
        $this->assertContains('backward_compatibility', $fillable);
        $this->assertContains('extra', $fillable);
        $this->assertNotContains('id', $fillable);
    }

    public function test_theme_translation_has_proper_casts(): void
    {
        $translation = new ThemeTranslation;
        $casts = $translation->getCasts();

        $this->assertArrayHasKey('extra', $casts);
        $this->assertEquals('object', $casts['extra']);
    }

    public function test_theme_translation_unique_ids_configuration(): void
    {
        $translation = new ThemeTranslation;

        $this->assertEquals(['id'], $translation->uniqueIds());
    }

    public function test_theme_translation_scope_methods_exist(): void
    {
        $translation = new ThemeTranslation;

        $this->assertTrue(method_exists($translation, 'scopeDefaultContext'));
        $this->assertTrue(method_exists($translation, 'scopeForLanguage'));
        $this->assertTrue(method_exists($translation, 'scopeForContext'));
    }

    public function test_theme_translation_respects_unique_constraint(): void
    {
        $theme = Theme::factory()->create();
        $language = Language::factory()->create();
        $context = Context::factory()->create();

        // Create first translation
        $translation1 = ThemeTranslation::factory()->create([
            'theme_id' => $theme->id,
            'language_id' => $language->id,
            'context_id' => $context->id,
        ]);

        // Attempting to create a second translation with the same combination should fail
        $this->expectException(\Illuminate\Database\QueryException::class);

        ThemeTranslation::factory()->create([
            'theme_id' => $theme->id,
            'language_id' => $language->id,
            'context_id' => $context->id,
        ]);
    }

    public function test_theme_translation_for_main_theme_and_subtheme(): void
    {
        $mainTheme = Theme::factory()->create(['parent_id' => null]);
        $subtheme = Theme::factory()->create(['parent_id' => $mainTheme->id]);

        $mainThemeTranslation = ThemeTranslation::factory()->create(['theme_id' => $mainTheme->id]);
        $subthemeTranslation = ThemeTranslation::factory()->create(['theme_id' => $subtheme->id]);

        $this->assertEquals($mainTheme->id, $mainThemeTranslation->theme_id);
        $this->assertEquals($subtheme->id, $subthemeTranslation->theme_id);
        $this->assertNull($mainTheme->parent_id);
        $this->assertEquals($mainTheme->id, $subtheme->parent_id);
    }
}
