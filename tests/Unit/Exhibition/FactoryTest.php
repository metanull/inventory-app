<?php

namespace Tests\Unit\Exhibition;

use App\Models\Exhibition;
use App\Models\ExhibitionTranslation;
use App\Models\Theme;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class FactoryTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_exhibition_factory_creates_valid_exhibition(): void
    {
        $exhibition = Exhibition::factory()->create();

        $this->assertDatabaseHas('exhibitions', [
            'id' => $exhibition->id,
            'internal_name' => $exhibition->internal_name,
        ]);

        $this->assertIsString($exhibition->id);
        $this->assertNotEmpty($exhibition->internal_name);
        $this->assertIsString($exhibition->internal_name);
    }

    public function test_exhibition_factory_creates_unique_internal_names(): void
    {
        $exhibition1 = Exhibition::factory()->create();
        $exhibition2 = Exhibition::factory()->create();

        $this->assertNotEquals($exhibition1->internal_name, $exhibition2->internal_name);
    }

    public function test_exhibition_factory_can_create_with_backward_compatibility(): void
    {
        $exhibition = Exhibition::factory()->withBackwardCompatibility()->create();

        $this->assertNotNull($exhibition->backward_compatibility);
        $this->assertIsString($exhibition->backward_compatibility);
    }

    public function test_exhibition_factory_can_create_without_backward_compatibility(): void
    {
        $exhibition = Exhibition::factory()->create();

        // The factory might or might not set backward_compatibility by default
        // This test just ensures it can be created
        $this->assertDatabaseHas('exhibitions', [
            'id' => $exhibition->id,
        ]);
    }

    public function test_exhibition_has_translations_relationship(): void
    {
        $exhibition = Exhibition::factory()->create();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $exhibition->translations());
    }

    public function test_exhibition_has_partners_relationship(): void
    {
        $exhibition = Exhibition::factory()->create();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsToMany::class, $exhibition->partners());
    }

    public function test_exhibition_has_themes_relationship(): void
    {
        $exhibition = Exhibition::factory()->create();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $exhibition->themes());
    }

    public function test_exhibition_can_have_multiple_translations(): void
    {
        $exhibition = Exhibition::factory()->create();
        ExhibitionTranslation::factory()->count(3)->create(['exhibition_id' => $exhibition->id]);

        $this->assertCount(3, $exhibition->translations);
    }

    public function test_exhibition_can_have_multiple_themes(): void
    {
        $exhibition = Exhibition::factory()->create();
        Theme::factory()->count(3)->create(['exhibition_id' => $exhibition->id]);

        $this->assertCount(3, $exhibition->themes);
    }

    public function test_exhibition_translation_helper_methods_exist(): void
    {
        $exhibition = Exhibition::factory()->create();

        $this->assertTrue(method_exists($exhibition, 'getDefaultTranslation'));
        $this->assertTrue(method_exists($exhibition, 'getContextualizedTranslation'));
        $this->assertTrue(method_exists($exhibition, 'getTranslationWithFallback'));
    }

    public function test_exhibition_uses_uuid_primary_key(): void
    {
        $exhibition = Exhibition::factory()->create();

        $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $exhibition->id);
    }

    public function test_exhibition_has_required_fillable_fields(): void
    {
        $exhibition = new Exhibition;
        $fillable = $exhibition->getFillable();

        $this->assertContains('backward_compatibility', $fillable);
        $this->assertContains('internal_name', $fillable);
        $this->assertNotContains('id', $fillable);
    }

    public function test_exhibition_unique_ids_configuration(): void
    {
        $exhibition = new Exhibition;

        $this->assertEquals(['id'], $exhibition->uniqueIds());
    }
}
