<?php

namespace Tests\Unit\Internationalization;

use App\Models\Author;
use App\Models\Contextualization;
use App\Models\Internationalization;
use App\Models\Language;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class FactoryTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_factory_creates_valid_internationalization(): void
    {
        $internationalization = Internationalization::factory()->create();

        $this->assertDatabaseHas('internationalizations', [
            'id' => $internationalization->id,
        ]);

        // Test required fields
        $this->assertNotNull($internationalization->contextualization_id);
        $this->assertNotNull($internationalization->language_id);
        $this->assertNotNull($internationalization->name);
        $this->assertNotNull($internationalization->description);

        // Test UUID format
        $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $internationalization->id);
        $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $internationalization->contextualization_id);

        // Test language_id format (3 characters)
        $this->assertEquals(3, strlen($internationalization->language_id));

        // Test timestamps
        $this->assertNotNull($internationalization->created_at);
        $this->assertNotNull($internationalization->updated_at);
    }

    public function test_factory_creates_internationalization_with_contextualization_relationship(): void
    {
        $internationalization = Internationalization::factory()->create();

        $contextualization = $internationalization->contextualization;
        $this->assertInstanceOf(Contextualization::class, $contextualization);
        $this->assertEquals($internationalization->contextualization_id, $contextualization->id);
    }

    public function test_factory_creates_internationalization_with_language_relationship(): void
    {
        $internationalization = Internationalization::factory()->create();

        $language = $internationalization->language;
        $this->assertInstanceOf(Language::class, $language);
        $this->assertEquals($internationalization->language_id, $language->id);
    }

    public function test_factory_creates_internationalization_with_optional_author_relationships(): void
    {
        $internationalization = Internationalization::factory()->create([
            'author_id' => Author::factory()->create()->id,
            'text_copy_editor_id' => Author::factory()->create()->id,
            'translator_id' => Author::factory()->create()->id,
            'translation_copy_editor_id' => Author::factory()->create()->id,
        ]);

        $this->assertInstanceOf(Author::class, $internationalization->author);
        $this->assertInstanceOf(Author::class, $internationalization->textCopyEditor);
        $this->assertInstanceOf(Author::class, $internationalization->translator);
        $this->assertInstanceOf(Author::class, $internationalization->translationCopyEditor);
    }

    public function test_factory_handles_nullable_fields(): void
    {
        $internationalization = Internationalization::factory()->create([
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

        $this->assertNull($internationalization->alternate_name);
        $this->assertNull($internationalization->type);
        $this->assertNull($internationalization->holder);
        $this->assertNull($internationalization->owner);
        $this->assertNull($internationalization->initial_owner);
        $this->assertNull($internationalization->dates);
        $this->assertNull($internationalization->location);
        $this->assertNull($internationalization->dimensions);
        $this->assertNull($internationalization->place_of_production);
        $this->assertNull($internationalization->method_for_datation);
        $this->assertNull($internationalization->method_for_provenance);
        $this->assertNull($internationalization->obtention);
        $this->assertNull($internationalization->bibliography);
        $this->assertNull($internationalization->extra);
        $this->assertNull($internationalization->author_id);
        $this->assertNull($internationalization->text_copy_editor_id);
        $this->assertNull($internationalization->translator_id);
        $this->assertNull($internationalization->translation_copy_editor_id);
        $this->assertNull($internationalization->backward_compatibility);
    }

    public function test_factory_handles_json_extra_field(): void
    {
        $extraData = ['notes' => 'Test note', 'additional_info' => 'Test info'];
        $internationalization = Internationalization::factory()->create([
            'extra' => $extraData,
        ]);

        $this->assertEquals($extraData, $internationalization->extra);
    }

    public function test_factory_enforces_unique_constraint_on_contextualization_language_combination(): void
    {
        $contextualization = Contextualization::factory()->create();
        $language = Language::factory()->create();

        // Create first internationalization
        Internationalization::factory()->create([
            'contextualization_id' => $contextualization->id,
            'language_id' => $language->id,
        ]);

        // Attempt to create duplicate should fail
        $this->expectException(QueryException::class);
        Internationalization::factory()->create([
            'contextualization_id' => $contextualization->id,
            'language_id' => $language->id,
        ]);
    }

    public function test_factory_creates_multiple_internationalizations_for_different_languages(): void
    {
        $contextualization = Contextualization::factory()->create();
        $language1 = Language::factory()->create(['id' => 'eng']);
        $language2 = Language::factory()->create(['id' => 'fra']);

        $internationalization1 = Internationalization::factory()->create([
            'contextualization_id' => $contextualization->id,
            'language_id' => $language1->id,
        ]);

        $internationalization2 = Internationalization::factory()->create([
            'contextualization_id' => $contextualization->id,
            'language_id' => $language2->id,
        ]);

        $this->assertEquals($contextualization->id, $internationalization1->contextualization_id);
        $this->assertEquals($contextualization->id, $internationalization2->contextualization_id);
        $this->assertNotEquals($internationalization1->language_id, $internationalization2->language_id);
    }

    public function test_factory_creates_internationalization_with_scopes(): void
    {
        Language::factory()->create(['id' => 'eng']);
        Language::factory()->create(['id' => 'fra', 'is_default' => true]);

        $englishInternationalization = Internationalization::factory()->create(['language_id' => 'eng']);
        $defaultInternationalization = Internationalization::factory()->create(['language_id' => 'fra']);

        // Test inEnglish scope
        $englishResults = Internationalization::inEnglish()->get();
        $this->assertTrue($englishResults->contains($englishInternationalization));
        $this->assertFalse($englishResults->contains($defaultInternationalization));

        // Test inDefaultLanguage scope
        $defaultResults = Internationalization::inDefaultLanguage()->get();
        $this->assertTrue($defaultResults->contains($defaultInternationalization));
        $this->assertFalse($defaultResults->contains($englishInternationalization));
    }

    public function test_factory_creates_internationalization_with_empty_string_conversion(): void
    {
        $internationalization = Internationalization::factory()->create([
            'alternate_name' => '',
            'type' => '',
            'holder' => '',
        ]);

        // Empty strings should be converted to null
        $this->assertNull($internationalization->alternate_name);
        $this->assertNull($internationalization->type);
        $this->assertNull($internationalization->holder);
    }
}
