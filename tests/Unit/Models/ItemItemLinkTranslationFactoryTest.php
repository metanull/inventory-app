<?php

namespace Tests\Unit\Models;

use App\Models\ItemItemLink;
use App\Models\ItemItemLinkTranslation;
use App\Models\Language;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests for ItemItemLinkTranslation factory.
 */
class ItemItemLinkTranslationFactoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_factory_creates_valid_item_item_link_translation(): void
    {
        $translation = ItemItemLinkTranslation::factory()->create();

        $this->assertInstanceOf(ItemItemLinkTranslation::class, $translation);
        $this->assertNotEmpty($translation->id);
        $this->assertNotEmpty($translation->item_item_link_id);
        $this->assertNotEmpty($translation->language_id);
    }

    public function test_factory_for_link_sets_correct_link_id(): void
    {
        $link = ItemItemLink::factory()->create();
        $translation = ItemItemLinkTranslation::factory()->forLink($link)->create();

        $this->assertEquals($link->id, $translation->item_item_link_id);
    }

    public function test_factory_for_language_sets_correct_language_id(): void
    {
        $language = Language::factory()->create();
        $translation = ItemItemLinkTranslation::factory()->forLanguage($language)->create();

        $this->assertEquals($language->id, $translation->language_id);
    }

    public function test_factory_for_language_accepts_string_id(): void
    {
        $language = Language::factory()->create();
        $translation = ItemItemLinkTranslation::factory()->forLanguage($language->id)->create();

        $this->assertEquals($language->id, $translation->language_id);
    }

    public function test_factory_with_backward_compatibility_sets_value(): void
    {
        $backwardCompat = 'test_backward_123';
        $translation = ItemItemLinkTranslation::factory()
            ->withBackwardCompatibility($backwardCompat)
            ->create();

        $this->assertEquals($backwardCompat, $translation->backward_compatibility);
    }

    public function test_factory_with_reciprocal_description_sets_value(): void
    {
        $description = 'Custom reciprocal description';
        $translation = ItemItemLinkTranslation::factory()
            ->withReciprocalDescription($description)
            ->create();

        $this->assertEquals($description, $translation->reciprocal_description);
    }

    public function test_factory_without_reciprocal_description_sets_null(): void
    {
        $translation = ItemItemLinkTranslation::factory()
            ->withoutReciprocalDescription()
            ->create();

        $this->assertNull($translation->reciprocal_description);
    }
}
