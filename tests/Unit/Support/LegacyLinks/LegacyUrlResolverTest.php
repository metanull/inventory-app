<?php

namespace Tests\Unit\Support\LegacyLinks;

use App\Enums\LegacyLinkConfidence;
use App\Enums\LegacyLinkType;
use App\Models\Collection;
use App\Models\Country;
use App\Models\Item;
use App\Models\Partner;
use App\Models\Project;
use App\Services\LegacyUrlResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LegacyUrlResolverTest extends TestCase
{
    use RefreshDatabase;

    private LegacyUrlResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resolver = new LegacyUrlResolver;
    }

    public function test_resolves_mwnf3_object_fixture(): void
    {
        $item = Item::factory()->Object()->create(['backward_compatibility' => 'mwnf3:objects:ISL:eg:Mus01:1']);

        $link = $this->resolver->resolveFor($item)->links[0];
        $links = $this->resolver->resolveFor($item)->links;

        $this->assertSame('https://islamicart.museumwnf.org/database_item.php?id=object;ISL;eg;Mus01;1;en', $link->url);
        $this->assertSame(LegacyLinkConfidence::EXACT, $link->confidence);
        $this->assertBackofficeUrl($links, 'https://virtual-office.museumwnf.org/?section=database/dba_objects&edit=1;ISL;eg;Mus01;1&');
    }

    public function test_resolves_mwnf3_object_with_mixed_case_project_code(): void
    {
        $item = Item::factory()->Object()->create(['backward_compatibility' => 'mwnf3:objects:GalEx5:uk:Mus92:2']);

        $link = $this->resolver->resolveFor($item)->links[0];
        $links = $this->resolver->resolveFor($item)->links;

        $this->assertSame('https://islamicart.museumwnf.org/database_item.php?id=object;GalEx5;uk;Mus92;2;en', $link->url);
        $this->assertSame(LegacyLinkConfidence::EXACT, $link->confidence);
        $this->assertBackofficeUrl($links, 'https://virtual-office.museumwnf.org/?section=database/dba_objects&edit=1;GalEx5;uk;Mus92;2&');
    }

    public function test_resolves_mwnf3_monument_fixture(): void
    {
        $item = Item::factory()->Monument()->create(['backward_compatibility' => 'mwnf3:monuments:BAR:pt:Mon11:23']);

        $link = $this->resolver->resolveFor($item)->links[0];
        $links = $this->resolver->resolveFor($item)->links;

        $this->assertSame('https://baroqueart.museumwnf.org/database_item.php?id=monument;BAR;pt;Mon11;23;en', $link->url);
        $this->assertSame(LegacyLinkConfidence::EXACT, $link->confidence);
        $this->assertBackofficeUrl($links, 'https://virtual-office.museumwnf.org/?section=database/dba_monuments&edit=1;BAR;pt;Mon11;23&');
    }

    public function test_resolves_sharing_history_object_fixture(): void
    {
        $item = Item::factory()->Object()->create(['backward_compatibility' => 'mwnf3_sharing_history:sh_objects:awe:at:26']);

        $link = $this->resolver->resolveFor($item)->links[0];
        $links = $this->resolver->resolveFor($item)->links;

        $this->assertSame('https://sharinghistory.museumwnf.org/database_item.php?id=object;awe;at;26;en', $link->url);
        $this->assertSame(LegacyLinkConfidence::EXACT, $link->confidence);
        $this->assertBackofficeUrl($links, 'https://virtual-office.museumwnf.org/?section=sh/sh_objects&edit=1;AWE;at;26&');
    }

    public function test_resolves_travels_trail_and_itinerary_fixtures(): void
    {
        $trail = Collection::factory()->exhibitionTrail()->create(['backward_compatibility' => 'mwnf3_travels:trail:IAM:pt:1']);
        $itinerary = Collection::factory()->itinerary()->create(['backward_compatibility' => 'mwnf3_travels:itinerary:IAM:pt:1:I']);

        $trailLink = $this->resolver->resolveFor($trail)->links[0];
        $itineraryLink = $this->resolver->resolveFor($itinerary)->links[0];

        $this->assertSame('https://travels.museumwnf.org/travel_et_trailDetail.php?id=IAM;pt;1;en&fl=its', $trailLink->url);
        $this->assertSame('https://travels.museumwnf.org/travel_et_itenary.php?id=IAM;pt;I;en;1&fl=des', $itineraryLink->url);
        $this->assertSame(LegacyLinkConfidence::EXACT, $trailLink->confidence);
        $this->assertSame(LegacyLinkConfidence::EXACT, $itineraryLink->confidence);
        $this->assertBackofficeUrl($this->resolver->resolveFor($trail)->links, 'https://virtual-office.museumwnf.org/?section=travel/trails&edit=1;IAM;pt;1&');
        $this->assertBackofficeUrl($this->resolver->resolveFor($itinerary)->links, 'https://virtual-office.museumwnf.org/?section=travel/trails&edit=2;IAM;pt;1;I&');
    }

    public function test_resolves_explore_country_fixture(): void
    {
        $collection = Collection::factory()->create(['backward_compatibility' => 'mwnf3_explore:country:eg']);

        $link = $this->resolver->resolveFor($collection)->links[0];
        $links = $this->resolver->resolveFor($collection)->links;

        $this->assertSame('https://explore.museumwnf.org/countries/c-eg', $link->url);
        $this->assertSame(LegacyLinkConfidence::EXACT, $link->confidence);
        $this->assertBackofficeUrl($links, 'https://virtual-office.museumwnf.org/?section=explore/explore_country&edit=1;eg&');
    }

    public function test_resolves_thematic_gallery_configured_fixture(): void
    {
        $collection = Collection::factory()->gallery()->create(['backward_compatibility' => 'mwnf3_thematic_gallery:thg_gallery:47']);

        $link = $this->resolver->resolveFor($collection)->links[0];
        $links = $this->resolver->resolveFor($collection)->links;

        $this->assertSame('https://exhibitions.museumwnf.org/the_use_of_colours_in_art/en', $link->url);
        $this->assertSame(LegacyLinkConfidence::EXACT, $link->confidence);
        $this->assertBackofficeUrl($links, 'https://virtual-office.museumwnf.org/?section=thg/thg_galleries&edit=1;47&');
    }

    public function test_mwnf3_object_keeps_project_link_and_adds_thematic_exhibition_participation_link(): void
    {
        $root = Collection::factory()->collection()->create([
            'internal_name' => 'thg_exhibitions_root',
            'backward_compatibility' => 'mwnf3_thematic_gallery:exhibitions_root',
        ]);
        $exhibition = Collection::factory()->exhibition()->withParent($root->id)->create([
            'internal_name' => 'exhibition_the_use_of_colours_in_art',
            'backward_compatibility' => 'mwnf3_thematic_gallery:thg_gallery:47',
        ]);
        $theme = Collection::factory()->theme()->withParent($exhibition->id)->create([
            'backward_compatibility' => 'mwnf3_thematic_gallery:theme:47:1',
        ]);
        $item = Item::factory()->Object()->create(['backward_compatibility' => 'mwnf3:objects:EXHCOLOUR:uk:Mus52:1']);

        $theme->attachedItems()->attach($item->id);

        $links = $this->resolver->resolveFor($item)->links;

        $this->assertPublicUrl($links, 'https://islamicart.museumwnf.org/database_item.php?id=object;EXHCOLOUR;uk;Mus52;1;en');
        $this->assertPublicUrl($links, 'https://exhibitions.museumwnf.org/the_use_of_colours_in_art/en/database-item/mwnf3/objects/EXHCOLOUR/uk/Mus52/1/en');
        $this->assertBackofficeUrl($links, 'https://virtual-office.museumwnf.org/?section=database/dba_objects&edit=1;EXHCOLOUR;uk;Mus52;1&');
        $this->assertSame(1, $this->countBackofficeLinks($links));
    }

    public function test_mwnf3_object_thematic_exhibition_link_uses_configured_gallery_path(): void
    {
        $root = Collection::factory()->collection()->create([
            'internal_name' => 'thg_exhibitions_root',
            'backward_compatibility' => 'mwnf3_thematic_gallery:exhibitions_root',
        ]);
        $exhibition = Collection::factory()->exhibition()->withParent($root->id)->create([
            'internal_name' => 'exhibition_lost_memories_along_the_hijaz_railway_from_istanbul_to_mecca',
            'backward_compatibility' => 'mwnf3_thematic_gallery:thg_gallery:55',
        ]);
        $item = Item::factory()->Object()->create(['backward_compatibility' => 'mwnf3:objects:GalEx5:uk:Mus92:2']);

        $exhibition->attachedItems()->attach($item->id);

        $links = $this->resolver->resolveFor($item)->links;

        $this->assertPublicUrl($links, 'https://islamicart.museumwnf.org/database_item.php?id=object;GalEx5;uk;Mus92;2;en');
        $this->assertPublicUrl($links, 'https://upgrade-exhibitions.museumwnf.org/the_hijaz_railway/en/database-item/mwnf3/objects/GalEx5/uk/Mus92/2/en');
        $this->assertSame(1, $this->countBackofficeLinks($links));
    }

    public function test_mwnf3_object_adds_thematic_gallery_participation_link_with_gallery_host(): void
    {
        $root = Collection::factory()->collection()->create([
            'internal_name' => 'thg_galleries_root',
            'backward_compatibility' => 'mwnf3_thematic_gallery:galleries_root',
        ]);
        $gallery = Collection::factory()->gallery()->withParent($root->id)->create([
            'internal_name' => 'gallery_clothing_and_costume',
            'backward_compatibility' => 'mwnf3_thematic_gallery:thg_gallery:12',
        ]);
        $item = Item::factory()->Object()->create(['backward_compatibility' => 'mwnf3:objects:ISL:jo:Mus01:4']);

        $gallery->attachedItems()->attach($item->id);

        $links = $this->resolver->resolveFor($item)->links;

        $this->assertPublicUrl($links, 'https://islamicart.museumwnf.org/database_item.php?id=object;ISL;jo;Mus01;4;en');
        $this->assertPublicUrl($links, 'https://clothing.museumwnf.org/database-item/mwnf3/objects/ISL/jo/Mus01/4/en');
        $this->assertBackofficeUrl($links, 'https://virtual-office.museumwnf.org/?section=database/dba_objects&edit=1;ISL;jo;Mus01;4&');
        $this->assertSame(1, $this->countBackofficeLinks($links));
    }

    public function test_mwnf3_object_adds_one_thematic_link_per_thematic_collection_participation(): void
    {
        $galleriesRoot = Collection::factory()->collection()->create([
            'internal_name' => 'thg_galleries_root',
            'backward_compatibility' => 'mwnf3_thematic_gallery:galleries_root',
        ]);
        $exhibitionsRoot = Collection::factory()->collection()->create([
            'internal_name' => 'thg_exhibitions_root',
            'backward_compatibility' => 'mwnf3_thematic_gallery:exhibitions_root',
        ]);
        $gallery = Collection::factory()->gallery()->withParent($galleriesRoot->id)->create([
            'backward_compatibility' => 'mwnf3_thematic_gallery:thg_gallery:12',
        ]);
        $exhibition = Collection::factory()->exhibition()->withParent($exhibitionsRoot->id)->create([
            'internal_name' => 'exhibition_the_use_of_colours_in_art',
            'backward_compatibility' => 'mwnf3_thematic_gallery:thg_gallery:47',
        ]);
        $item = Item::factory()->Object()->create(['backward_compatibility' => 'mwnf3:objects:EXHCOLOUR:uk:Mus52:1']);

        $item->attachedToCollections()->attach([$gallery->id, $exhibition->id]);

        $links = $this->resolver->resolveFor($item)->links;

        $this->assertPublicUrl($links, 'https://clothing.museumwnf.org/database-item/mwnf3/objects/EXHCOLOUR/uk/Mus52/1/en');
        $this->assertPublicUrl($links, 'https://exhibitions.museumwnf.org/the_use_of_colours_in_art/en/database-item/mwnf3/objects/EXHCOLOUR/uk/Mus52/1/en');
        $this->assertSame(1, $this->countBackofficeLinks($links));
    }

    public function test_resolves_mwnf3_partner_with_project_context(): void
    {
        $project = Project::factory()->create(['backward_compatibility' => 'ISL']);
        $partner = Partner::factory()->Museum()->create([
            'backward_compatibility' => 'mwnf3:museums:Mus01:eg',
            'project_id' => $project->id,
        ]);

        $link = $this->resolver->resolveFor($partner)->links[0];
        $links = $this->resolver->resolveFor($partner)->links;

        $this->assertSame('https://islamicart.museumwnf.org/pm_partner.php?id=Mus01;eg&type=museum&theme=ISL', $link->url);
        $this->assertSame(LegacyLinkConfidence::INFERRED, $link->confidence);
        $this->assertBackofficeUrl($links, 'https://virtual-office.museumwnf.org/?section=database/museum&edit=1;Mus01;eg&');
    }

    public function test_resolves_explore_location_with_country_context(): void
    {
        $country = Country::factory()->create(['id' => 'egy', 'backward_compatibility' => 'eg']);
        $collection = Collection::factory()->location()->create([
            'backward_compatibility' => 'mwnf3_explore:location:2',
            'country_id' => $country->id,
        ]);

        $link = $this->resolver->resolveFor($collection)->links[0];
        $links = $this->resolver->resolveFor($collection)->links;

        $this->assertSame('https://explore.museumwnf.org/countries/c-eg/l-2', $link->url);
        $this->assertSame(LegacyLinkConfidence::INFERRED, $link->confidence);
        $this->assertBackofficeUrl($links, 'https://virtual-office.museumwnf.org/?section=explore/explore_locations&edit=1;2&');
    }

    public function test_unresolved_partner_mapping_returns_visible_diagnostic(): void
    {
        $partner = Partner::factory()->Museum()->create(['backward_compatibility' => 'mwnf3:museums:Mus01:eg']);

        $link = $this->resolver->resolveFor($partner)->links[0];

        $this->assertNull($link->url);
        $this->assertSame(LegacyLinkConfidence::REQUIRES_LOOKUP, $link->confidence);
        $this->assertStringContainsString('project code', $link->note);
    }

    private function assertBackofficeUrl(array $links, string $expectedUrl): void
    {
        $backofficeLinks = array_values(array_filter(
            $links,
            fn ($link): bool => $link->type === LegacyLinkType::BACKOFFICE,
        ));

        $this->assertNotEmpty($backofficeLinks);
        $this->assertSame($expectedUrl, $backofficeLinks[0]->url);
    }

    private function assertPublicUrl(array $links, string $expectedUrl): void
    {
        $this->assertContains($expectedUrl, array_map(
            fn ($link): ?string => $link->type === LegacyLinkType::PUBLIC ? $link->url : null,
            $links,
        ));
    }

    private function countBackofficeLinks(array $links): int
    {
        return count(array_filter(
            $links,
            fn ($link): bool => $link->type === LegacyLinkType::BACKOFFICE,
        ));
    }
}
