<?php

namespace Tests\Unit\PictureTranslation;

use App\Models\Author;
use App\Models\Context;
use App\Models\Language;
use App\Models\Picture;
use App\Models\PictureTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class FactoryTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_factory_creates_valid_picture_translation(): void
    {
        $pictureTranslation = PictureTranslation::factory()->create();

        $this->assertDatabaseHas('picture_translations', [
            'id' => $pictureTranslation->id,
            'picture_id' => $pictureTranslation->picture_id,
            'language_id' => $pictureTranslation->language_id,
            'context_id' => $pictureTranslation->context_id,
            'description' => $pictureTranslation->description,
            'caption' => $pictureTranslation->caption,
        ]);

        $this->assertInstanceOf(PictureTranslation::class, $pictureTranslation);
        $this->assertIsString($pictureTranslation->id);
        $this->assertIsString($pictureTranslation->picture_id);
        $this->assertIsString($pictureTranslation->language_id);
        $this->assertIsString($pictureTranslation->context_id);
        $this->assertIsString($pictureTranslation->description);
        $this->assertIsString($pictureTranslation->caption);
    }

    public function test_factory_creates_picture_translation_with_relationships(): void
    {
        $picture = Picture::factory()->create();
        $language = Language::factory()->create();
        $context = Context::factory()->create();

        $pictureTranslation = PictureTranslation::factory()
            ->forPicture($picture)
            ->forLanguage($language)
            ->forContext($context)
            ->create();

        $this->assertEquals($picture->id, $pictureTranslation->picture_id);
        $this->assertEquals($language->id, $pictureTranslation->language_id);
        $this->assertEquals($context->id, $pictureTranslation->context_id);
        $this->assertInstanceOf(Picture::class, $pictureTranslation->picture);
        $this->assertInstanceOf(Language::class, $pictureTranslation->language);
        $this->assertInstanceOf(Context::class, $pictureTranslation->context);
    }

    public function test_factory_creates_picture_translation_with_default_context(): void
    {
        $defaultContext = Context::factory()->default()->create();
        $pictureTranslation = PictureTranslation::factory()->defaultContext()->create();

        $this->assertEquals($defaultContext->id, $pictureTranslation->context_id);
        $this->assertTrue($pictureTranslation->context->is_default);
    }

    public function test_factory_creates_picture_translation_with_authors(): void
    {
        $pictureTranslation = PictureTranslation::factory()->withAuthors()->create();

        $this->assertNotNull($pictureTranslation->author_id);
        $this->assertNotNull($pictureTranslation->text_copy_editor_id);
        $this->assertNotNull($pictureTranslation->translator_id);
        $this->assertNotNull($pictureTranslation->translation_copy_editor_id);
        $this->assertInstanceOf(Author::class, $pictureTranslation->author);
        $this->assertInstanceOf(Author::class, $pictureTranslation->textCopyEditor);
        $this->assertInstanceOf(Author::class, $pictureTranslation->translator);
        $this->assertInstanceOf(Author::class, $pictureTranslation->translationCopyEditor);
    }

    public function test_factory_creates_picture_translation_with_extra_data(): void
    {
        $extraData = ['custom_field' => 'custom_value', 'another_field' => 'another_value'];
        $pictureTranslation = PictureTranslation::factory()->withExtra($extraData)->create();

        $this->assertEquals($extraData, $pictureTranslation->extra);
    }

    public function test_factory_creates_picture_translation_with_language_states(): void
    {
        $englishTranslation = PictureTranslation::factory()->english()->create();
        $frenchTranslation = PictureTranslation::factory()->french()->create();
        $spanishTranslation = PictureTranslation::factory()->spanish()->create();
        $arabicTranslation = PictureTranslation::factory()->arabic()->create();

        $this->assertEquals('eng', $englishTranslation->language_id);
        $this->assertEquals('fre', $frenchTranslation->language_id);
        $this->assertEquals('spa', $spanishTranslation->language_id);
        $this->assertEquals('ara', $arabicTranslation->language_id);
    }

    public function test_factory_respects_unique_constraint(): void
    {
        $picture = Picture::factory()->create();
        $language = Language::factory()->create();
        $context = Context::factory()->create();

        // Create first translation
        PictureTranslation::factory()
            ->forPicture($picture)
            ->forLanguage($language)
            ->forContext($context)
            ->create();

        // Attempt to create duplicate should fail
        $this->expectException(\Illuminate\Database\QueryException::class);
        PictureTranslation::factory()
            ->forPicture($picture)
            ->forLanguage($language)
            ->forContext($context)
            ->create();
    }

    public function test_factory_allows_nullable_fields(): void
    {
        $pictureTranslation = PictureTranslation::factory()->create([
            'author_id' => null,
            'text_copy_editor_id' => null,
            'translator_id' => null,
            'translation_copy_editor_id' => null,
            'backward_compatibility' => null,
            'extra' => null,
        ]);

        $this->assertNull($pictureTranslation->author_id);
        $this->assertNull($pictureTranslation->text_copy_editor_id);
        $this->assertNull($pictureTranslation->translator_id);
        $this->assertNull($pictureTranslation->translation_copy_editor_id);
        $this->assertNull($pictureTranslation->backward_compatibility);
        $this->assertNull($pictureTranslation->extra);
    }

    public function test_factory_validates_required_fields(): void
    {
        $this->expectException(\Illuminate\Database\QueryException::class);
        PictureTranslation::factory()->create([
            'description' => null,
        ]);
    }

    public function test_factory_validates_caption_required(): void
    {
        $this->expectException(\Illuminate\Database\QueryException::class);
        PictureTranslation::factory()->create([
            'caption' => null,
        ]);
    }
}
