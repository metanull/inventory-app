<?php

namespace Tests\Unit;

use App\Models\Language;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class LanguageTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_factory(): void
    {
        $language = Language::factory()->create();

        $this->assertInstanceOf(Language::class, $language);
        $this->assertNotEmpty($language->id);
        $this->assertNotEmpty($language->internal_name);
        $this->assertNotEmpty($language->backward_compatibility);
        $this->assertFalse($language->is_default);
    }

    public function test_factory_with_is_default(): void
    {
        $language = Language::factory()->withIsDefault()->create();

        $this->assertTrue($language->is_default);
    }

    public function test_factory_creates_a_row_in_database(): void
    {
        $language = Language::factory()->create();

        $this->assertDatabaseHas('languages', [
            'id' => $language->id,
            'internal_name' => $language->internal_name,
            'backward_compatibility' => $language->backward_compatibility,
            'is_default' => $language->is_default,
        ]);
    }

    public function test_factory_creates_a_row_in_database_without_is_default(): void
    {
        $language = Language::factory()->create();

        $this->assertDatabaseHas('languages', [
            'id' => $language->id,
            'internal_name' => $language->internal_name,
            'backward_compatibility' => $language->backward_compatibility,
            'is_default' => false,
        ]);
    }

    public function test_factory_creates_a_row_in_database_with_is_default(): void
    {
        $language = Language::factory()->withIsDefault()->create();

        $this->assertDatabaseHas('languages', [
            'id' => $language->id,
            'internal_name' => $language->internal_name,
            'backward_compatibility' => $language->backward_compatibility,
            'is_default' => true,
        ]);
    }

    public function test_model_scope_default(): void
    {
        $defaultLanguage = Language::factory()->withIsDefault()->create();
        $otherLanguage = Language::factory()->create();

        $this->assertEquals($defaultLanguage->id, Language::default()->first()->id);
        $this->assertNotEquals($otherLanguage->id, Language::default()->first()->id);
    }

    public function test_model_scope_english(): void
    {
        $englishLanguage = Language::factory()->create(['id' => 'eng']);
        $otherLanguage = Language::factory()->create();

        $this->assertEquals($englishLanguage->id, Language::english()->first()->id);
        $this->assertNotEquals($otherLanguage->id, Language::english()->first()->id);
    }

    public function test_model_method_setDefault(): void
    {
        $language1 = Language::factory()->create();
        $language2 = Language::factory()->create();
        $this->assertFalse($language1->fresh()->is_default);
        $this->assertFalse($language2->fresh()->is_default);
        $language1->setDefault();
        $this->assertTrue($language1->fresh()->is_default);
        $this->assertFalse($language2->fresh()->is_default);
        $language2->setDefault();
        $this->assertFalse($language1->fresh()->is_default);
        $this->assertTrue($language2->fresh()->is_default);
        $this->assertEquals(1, Language::where('is_default', true)->count());
    }
}
