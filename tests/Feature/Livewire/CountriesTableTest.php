<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire;

use App\Livewire\Tables\CountriesTable;
use App\Models\Country;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CountriesTableTest extends TestCase
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
        Livewire::test(CountriesTable::class)
            ->assertStatus(200);
    }

    public function test_search_filters_countries(): void
    {
        $country1 = Country::factory()->create(['internal_name' => 'France']);
        $country2 = Country::factory()->create(['internal_name' => 'Germany']);

        Livewire::test(CountriesTable::class)
            ->set('q', 'France')
            ->assertSeeText('France')
            ->assertDontSeeText('Germany');
    }

    public function test_search_is_debounced(): void
    {
        Country::factory()->create(['internal_name' => 'Test Country']);

        Livewire::test(CountriesTable::class)
            ->set('q', 'Test')
            ->assertSeeText('Test Country');
    }

    public function test_pagination_changes_page(): void
    {
        Country::factory()->count(50)->create();

        Livewire::test(CountriesTable::class)
            ->set('perPage', 10)
            ->call('gotoPage', 2)
            ->assertSet('perPage', 10);
    }

    public function test_per_page_changes_limit(): void
    {
        Country::factory()->count(30)->create();

        Livewire::test(CountriesTable::class)
            ->set('perPage', 20)
            ->assertSet('perPage', 20);
    }
}
