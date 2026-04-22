<?php

namespace Tests\Unit\Services;

use App\Models\Context;
use App\Models\Item;
use App\Models\Language;
use App\Models\Partner;
use App\Models\PartnerImage;
use App\Models\PartnerTranslation;
use App\Services\Web\PartnerShowPageData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PartnerShowPageDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_build_preloads_all_relations_used_by_partner_show_page(): void
    {
        $defaultLanguage = Language::factory()->withIsDefault()->create(['id' => 'eng']);
        $defaultContext = Context::factory()->withIsDefault()->create();
        $monument = Item::factory()->Monument()->create();
        $partner = Partner::factory()->withCountry()->withProject()->create(['monument_item_id' => $monument->id]);

        PartnerImage::factory()->create(['partner_id' => $partner->id]);
        PartnerTranslation::factory()->forPartner($partner->id)->forLanguage($defaultLanguage->id)->forContext($defaultContext->id)->create();

        DB::flushQueryLog();
        DB::enableQueryLog();

        $partner = $partner->fresh();
        $pageData = app(PartnerShowPageData::class)->build($partner);
        $queryCountAfterBuild = count(DB::getQueryLog());

        $partner->country?->internal_name;
        $partner->project?->internal_name;
        $partner->monumentItem?->internal_name;
        $this->assertArrayHasKey('sections', $pageData);
        $this->assertSame(
            ['images', 'translations', 'monument', 'system'],
            array_keys($pageData['sections'])
        );
        $this->assertArrayHasKey('item', $pageData['sections']['monument']);
        $this->assertArrayNotHasKey('options', $pageData['sections']['monument']);

        $pageData['sections']['images']['images']->first()?->alt_text;
        $pageData['sections']['translations']['groups']->first()['translations']->first()?->language?->internal_name;
        $pageData['sections']['monument']['item']?->internal_name;
        $pageData['sections']['system']['id'];

        $this->assertCount($queryCountAfterBuild, DB::getQueryLog());
        $this->assertTrue($partner->relationLoaded('country'));
        $this->assertTrue($partner->relationLoaded('project'));
        $this->assertTrue($partner->relationLoaded('monumentItem'));

        DB::disableQueryLog();
    }
}
