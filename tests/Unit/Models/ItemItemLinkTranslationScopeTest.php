<?php

namespace Tests\Unit\Models;

use App\Models\ItemItemLink;
use App\Models\ItemItemLinkTranslation;
use App\Models\Language;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests for ItemItemLinkTranslation model scopes.
 *
 * These tests verify the business logic of model query scopes.
 */
class ItemItemLinkTranslationScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_scope_for_link_filters_by_item_item_link(): void
    {
        $link1 = ItemItemLink::factory()->create();
        $link2 = ItemItemLink::factory()->create();

        $translation1 = ItemItemLinkTranslation::factory()->forLink($link1)->create();
        $translation2 = ItemItemLinkTranslation::factory()->forLink($link2)->create();

        $results = ItemItemLinkTranslation::forLink($link1)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains('id', $translation1->id));
        $this->assertFalse($results->contains('id', $translation2->id));
    }

    public function test_scope_for_link_accepts_string_id(): void
    {
        $link = ItemItemLink::factory()->create();
        $translation = ItemItemLinkTranslation::factory()->forLink($link)->create();

        $results = ItemItemLinkTranslation::forLink($link->id)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains('id', $translation->id));
    }

    public function test_scope_for_link_accepts_model_instance(): void
    {
        $link = ItemItemLink::factory()->create();
        $translation = ItemItemLinkTranslation::factory()->forLink($link)->create();

        $results = ItemItemLinkTranslation::forLink($link)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains('id', $translation->id));
    }

    public function test_scope_for_language_filters_by_language(): void
    {
        $language1 = Language::factory()->create();
        $language2 = Language::factory()->create();

        $translation1 = ItemItemLinkTranslation::factory()->forLanguage($language1)->create();
        $translation2 = ItemItemLinkTranslation::factory()->forLanguage($language2)->create();

        $results = ItemItemLinkTranslation::forLanguage($language1)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains('id', $translation1->id));
        $this->assertFalse($results->contains('id', $translation2->id));
    }

    public function test_scope_for_language_accepts_string_id(): void
    {
        $language = Language::factory()->create();
        $translation = ItemItemLinkTranslation::factory()->forLanguage($language)->create();

        $results = ItemItemLinkTranslation::forLanguage($language->id)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains('id', $translation->id));
    }

    public function test_scope_for_language_accepts_model_instance(): void
    {
        $language = Language::factory()->create();
        $translation = ItemItemLinkTranslation::factory()->forLanguage($language)->create();

        $results = ItemItemLinkTranslation::forLanguage($language)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains('id', $translation->id));
    }

    public function test_scopes_can_be_chained(): void
    {
        $link = ItemItemLink::factory()->create();
        $language = Language::factory()->create();

        $matchingTranslation = ItemItemLinkTranslation::factory()
            ->forLink($link)
            ->forLanguage($language)
            ->create();

        // Create non-matching translations
        ItemItemLinkTranslation::factory()
            ->forLink($link)
            ->create();
        ItemItemLinkTranslation::factory()
            ->forLanguage($language)
            ->create();

        $results = ItemItemLinkTranslation::forLink($link)
            ->forLanguage($language)
            ->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains('id', $matchingTranslation->id));
    }
}
