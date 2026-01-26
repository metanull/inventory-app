<?php

namespace Tests\Unit\Models;

use App\Models\ItemItemLink;
use App\Models\ItemItemLinkTranslation;
use App\Models\Language;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests for ItemItemLinkTranslation model relationships.
 *
 * These tests verify the business logic of model relationships.
 */
class ItemItemLinkTranslationRelationshipTest extends TestCase
{
    use RefreshDatabase;

    public function test_translation_belongs_to_item_item_link(): void
    {
        $link = ItemItemLink::factory()->create();
        $translation = ItemItemLinkTranslation::factory()->forLink($link)->create();

        $this->assertInstanceOf(ItemItemLink::class, $translation->itemItemLink);
        $this->assertEquals($link->id, $translation->itemItemLink->id);
    }

    public function test_translation_belongs_to_language(): void
    {
        $language = Language::factory()->create();
        $translation = ItemItemLinkTranslation::factory()->forLanguage($language)->create();

        $this->assertInstanceOf(Language::class, $translation->language);
        $this->assertEquals($language->id, $translation->language->id);
    }

    public function test_item_item_link_has_many_translations(): void
    {
        $link = ItemItemLink::factory()->create();
        $language1 = Language::factory()->create();
        $language2 = Language::factory()->create();

        ItemItemLinkTranslation::factory()
            ->forLink($link)
            ->forLanguage($language1)
            ->create();

        ItemItemLinkTranslation::factory()
            ->forLink($link)
            ->forLanguage($language2)
            ->create();

        $this->assertCount(2, $link->translations);
        $this->assertInstanceOf(ItemItemLinkTranslation::class, $link->translations->first());
    }

    public function test_deleting_link_cascades_to_translations(): void
    {
        $link = ItemItemLink::factory()->create();
        $translation = ItemItemLinkTranslation::factory()->forLink($link)->create();

        $translationId = $translation->id;

        $link->delete();

        $this->assertDatabaseMissing('item_item_link_translations', ['id' => $translationId]);
    }
}
