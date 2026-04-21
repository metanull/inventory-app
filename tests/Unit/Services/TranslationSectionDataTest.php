<?php

namespace Tests\Unit\Services;

use App\Models\Context;
use App\Models\Item;
use App\Models\ItemTranslation;
use App\Models\Language;
use App\Services\Web\TranslationSectionData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TranslationSectionDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_build_groups_by_context_and_prioritizes_defaults(): void
    {
        $defaultLanguage = Language::factory()->withIsDefault()->create(['id' => 'eng', 'internal_name' => 'English']);
        $otherLanguage = Language::factory()->create(['id' => 'ita', 'internal_name' => 'Italian']);
        $defaultContext = Context::factory()->withIsDefault()->create(['internal_name' => 'Default Context']);
        $otherContext = Context::factory()->create(['internal_name' => 'Secondary Context']);
        $item = Item::factory()->create();

        ItemTranslation::factory()->forItem($item->id)->forLanguage($otherLanguage->id)->forContext($otherContext->id)->create(['name' => 'Secondary Italian']);
        ItemTranslation::factory()->forItem($item->id)->forLanguage($defaultLanguage->id)->forContext($otherContext->id)->create(['name' => 'Secondary English']);
        ItemTranslation::factory()->forItem($item->id)->forLanguage($otherLanguage->id)->forContext($defaultContext->id)->create(['name' => 'Default Italian']);
        ItemTranslation::factory()->forItem($item->id)->forLanguage($defaultLanguage->id)->forContext($defaultContext->id)->create(['name' => 'Default English']);

        $item->load(['translations.context', 'translations.language']);

        $groups = app(TranslationSectionData::class)->build($item->translations);

        $this->assertCount(2, $groups);
        $this->assertSame('Default Context', $groups->first()['label']);
        $this->assertTrue($groups->first()['is_default']);
        $this->assertSame('Default English', $groups->first()['translations']->first()->name);
        $this->assertSame('Secondary Context', $groups->last()['label']);
        $this->assertSame('Secondary English', $groups->last()['translations']->first()->name);
    }
}
