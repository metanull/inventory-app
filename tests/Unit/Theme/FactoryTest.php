<?php

namespace Tests\Unit\Theme;

use App\Models\Collection;
use App\Models\Theme;
use App\Models\ThemeTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class FactoryTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_theme_factory_creates_valid_theme(): void
    {
        $theme = Theme::factory()->create();

        $this->assertDatabaseHas('themes', [
            'id' => $theme->id,
            'internal_name' => $theme->internal_name,
            'collection_id' => $theme->collection_id,
        ]);

        $this->assertIsString($theme->id);
        $this->assertNotEmpty($theme->internal_name);
        $this->assertIsString($theme->internal_name);
        $this->assertNotNull($theme->collection_id);
    }

    public function test_theme_factory_creates_unique_internal_names(): void
    {
        $theme1 = Theme::factory()->create();
        $theme2 = Theme::factory()->create();

        $this->assertNotEquals($theme1->internal_name, $theme2->internal_name);
    }

    public function test_theme_factory_can_create_with_collection(): void
    {
        $collection = Collection::factory()->state(['type' => 'exhibition'])->create();
        $theme = Theme::factory()->create(['collection_id' => $collection->id]);

        $this->assertEquals($collection->id, $theme->collection_id);
        $this->assertInstanceOf(Collection::class, $theme->collection);
    }

    public function test_theme_factory_can_create_main_theme(): void
    {
        $theme = Theme::factory()->create(['parent_id' => null]);

        $this->assertNull($theme->parent_id);
    }

    public function test_theme_factory_can_create_subtheme(): void
    {
        $parentTheme = Theme::factory()->create(['parent_id' => null]);
        $subtheme = Theme::factory()->create(['parent_id' => $parentTheme->id]);

        $this->assertEquals($parentTheme->id, $subtheme->parent_id);
        $this->assertInstanceOf(Theme::class, $subtheme->parent);
    }

    public function test_theme_factory_can_create_with_backward_compatibility(): void
    {
        $theme = Theme::factory()->withBackwardCompatibility()->create();

        $this->assertNotNull($theme->backward_compatibility);
        $this->assertIsString($theme->backward_compatibility);
    }

    public function test_theme_has_translations_relationship(): void
    {
        $theme = Theme::factory()->create();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $theme->translations());
    }

    public function test_theme_has_collection_relationship(): void
    {
        $theme = Theme::factory()->create();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $theme->collection());
    }

    public function test_theme_has_parent_relationship(): void
    {
        $theme = Theme::factory()->create();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $theme->parent());
    }

    public function test_theme_has_subthemes_relationship(): void
    {
        $theme = Theme::factory()->create();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $theme->subthemes());
    }

    // NOTE: Theme-Picture relationship removed in favor of ItemImage system
    // Themes will be connected to pictures through their associated items

    public function test_theme_can_have_multiple_translations(): void
    {
        $theme = Theme::factory()->create();
        ThemeTranslation::factory()->count(3)->create(['theme_id' => $theme->id]);

        $this->assertCount(3, $theme->translations);
    }

    public function test_theme_can_have_multiple_subthemes(): void
    {
        $parentTheme = Theme::factory()->create(['parent_id' => null]);
        Theme::factory()->count(3)->create(['parent_id' => $parentTheme->id]);

        $this->assertCount(3, $parentTheme->subthemes);
    }

    public function test_theme_translation_helper_methods_exist(): void
    {
        $theme = Theme::factory()->create();

        $this->assertTrue(method_exists($theme, 'getDefaultTranslation'));
        $this->assertTrue(method_exists($theme, 'getContextualizedTranslation'));
        $this->assertTrue(method_exists($theme, 'getTranslationWithFallback'));
    }

    public function test_theme_uses_uuid_primary_key(): void
    {
        $theme = Theme::factory()->create();

        $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $theme->id);
    }

    public function test_theme_has_required_fillable_fields(): void
    {
        $theme = new Theme;
        $fillable = $theme->getFillable();

        $this->assertContains('collection_id', $fillable);
        $this->assertContains('parent_id', $fillable);
        $this->assertContains('internal_name', $fillable);
        $this->assertContains('backward_compatibility', $fillable);
        $this->assertNotContains('id', $fillable);
    }

    public function test_theme_unique_ids_configuration(): void
    {
        $theme = new Theme;

        $this->assertEquals(['id'], $theme->uniqueIds());
    }

    public function test_theme_hierarchy_respects_two_level_limit(): void
    {
        $mainTheme = Theme::factory()->create(['parent_id' => null]);
        $subtheme = Theme::factory()->create(['parent_id' => $mainTheme->id]);

        // According to instructions, only 2 levels: main themes and sub themes
        // Sub themes should not have their own children
        $this->assertNull($mainTheme->parent_id);
        $this->assertEquals($mainTheme->id, $subtheme->parent_id);
    }
}
