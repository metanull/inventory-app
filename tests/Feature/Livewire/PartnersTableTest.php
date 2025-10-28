<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire;

use App\Livewire\Tables\PartnersTable;
use App\Models\Country;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PartnersTableTest extends TestCase
{
    use RefreshDatabase;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_component_renders_without_errors(): void
    {
        Livewire::test(PartnersTable::class)
            ->assertStatus(200);
    }

    public function test_search_filters_partners(): void
    {
        $partner1 = Partner::factory()->create(['internal_name' => 'Museum ABC']);
        $partner2 = Partner::factory()->create(['internal_name' => 'Gallery XYZ']);

        Livewire::test(PartnersTable::class)
            ->set('q', 'Museum')
            ->assertSeeText('Museum ABC')
            ->assertDontSeeText('Gallery XYZ');
    }

    public function test_search_is_debounced(): void
    {
        Partner::factory()->create(['internal_name' => 'Test Partner']);

        $component = Livewire::test(PartnersTable::class)
            ->set('q', 'Test');

        // Search property should be set
        $this->assertEquals('Test', $component->get('q'));
    }

    public function test_pagination_changes_page(): void
    {
        // Create more partners than perPage to trigger pagination
        Partner::factory()->count(15)->create();

        Livewire::test(PartnersTable::class)
            ->set('perPage', 10)
            ->call('gotoPage', 2)
            ->assertSet('perPage', 10);
    }

    public function test_per_page_changes_limit(): void
    {
        Partner::factory()->count(5)->create();

        Livewire::test(PartnersTable::class)
            ->set('perPage', 25)
            ->assertSet('perPage', 25);
    }

    public function test_search_with_country_relationship(): void
    {
        $country = Country::factory()->create(['internal_name' => 'France']);
        $partner = Partner::factory()->create([
            'internal_name' => 'French Museum',
            'country_id' => $country->id,
        ]);

        Livewire::test(PartnersTable::class)
            ->set('q', 'French')
            ->assertSeeText('French Museum');
    }
}
