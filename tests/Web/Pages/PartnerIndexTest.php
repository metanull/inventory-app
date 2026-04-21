<?php

namespace Tests\Web\Pages;

use App\Models\Country;
use App\Models\Partner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Web\Traits\AuthenticatesWebRequests;

class PartnerIndexTest extends TestCase
{
    use AuthenticatesWebRequests;
    use RefreshDatabase;

    public function test_index_renders_request_driven_partner_page_without_livewire_markup(): void
    {
        Partner::factory()->create(['internal_name' => 'Museum Alpha']);

        $response = $this->get(route('partners.index'));

        $response
            ->assertOk()
            ->assertViewIs('partners.index')
            ->assertSee('Museum Alpha');

        $this->assertStringNotContainsString('livewire:dynamic-component', $response->getContent());
        $this->assertStringNotContainsString('wire:click', $response->getContent());
    }

    public function test_index_can_search_by_internal_name(): void
    {
        Partner::factory()->create(['internal_name' => 'Museum Alpha']);
        Partner::factory()->create(['internal_name' => 'Other Partner']);

        $response = $this->get(route('partners.index', ['q' => 'Museum']));

        $response
            ->assertOk()
            ->assertSee('Museum Alpha')
            ->assertDontSee('Other Partner');
    }

    public function test_index_can_sort_by_internal_name(): void
    {
        Partner::factory()->create(['internal_name' => 'Zulu Partner']);
        Partner::factory()->create(['internal_name' => 'Alpha Partner']);

        $response = $this->get(route('partners.index', [
            'sort' => 'internal_name',
            'direction' => 'asc',
        ]));

        $response
            ->assertOk()
            ->assertSeeInOrder(['Alpha Partner', 'Zulu Partner']);
    }

    public function test_index_eager_loads_country_for_rendered_columns(): void
    {
        $country = Country::factory()->create(['internal_name' => 'Jordan']);
        Partner::factory()->create([
            'internal_name' => 'Museum Alpha',
            'country_id' => $country->id,
        ]);

        $response = $this->get(route('partners.index'));

        $response
            ->assertOk()
            ->assertSee('Jordan');
    }

    public function test_index_preserves_query_strings_in_pagination_and_sort_links(): void
    {
        foreach (range(1, 11) as $index) {
            Partner::factory()->create([
                'internal_name' => 'Museum '.str_pad((string) $index, 2, '0', STR_PAD_LEFT),
            ]);
        }

        $response = $this->get(route('partners.index', [
            'q' => 'Museum',
            'per_page' => 10,
            'sort' => 'internal_name',
            'direction' => 'asc',
        ]));

        $response->assertOk();

        $paginator = $response->viewData('partners');
        $nextPageUrl = $paginator->nextPageUrl();

        $this->assertNotNull($nextPageUrl);
        $this->assertStringContainsString('q=Museum', $nextPageUrl);
        $this->assertStringContainsString('per_page=10', $nextPageUrl);
        $this->assertStringContainsString(
            'href="http://localhost/web/partners?q=Museum&amp;per_page=10&amp;sort=created_at&amp;direction=asc&amp;page=1"',
            $response->getContent(),
        );
    }
}
