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
}
