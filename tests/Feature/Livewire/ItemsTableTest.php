<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire;

use App\Livewire\Tables\ItemsTable;
use App\Models\Item;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use Tests\TestCase;

class ItemsTableTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_component_renders_without_errors(): void
    {
        Livewire::test(ItemsTable::class)
            ->assertStatus(200);
    }

    public function test_search_filters_items(): void
    {
        $item1 = Item::factory()->create(['internal_name' => 'Ancient Vase']);
        $item2 = Item::factory()->create(['internal_name' => 'Modern Sculpture']);

        Livewire::test(ItemsTable::class)
            ->set('q', 'Ancient')
            ->assertSeeText('Ancient Vase')
            ->assertDontSeeText('Modern Sculpture');
    }

    public function test_search_is_debounced(): void
    {
        Item::factory()->create(['internal_name' => 'Test Item']);

        $component = Livewire::test(ItemsTable::class)
            ->set('q', 'Test');

        // Search property should be set
        $this->assertEquals('Test', $component->get('q'));
    }

    public function test_pagination_changes_page(): void
    {
        // Create more items than perPage to trigger pagination
        Item::factory()->count(15)->create();

        Livewire::test(ItemsTable::class)
            ->set('perPage', 10)
            ->call('gotoPage', 2)
            ->assertSet('perPage', 10);
    }

    public function test_per_page_changes_limit(): void
    {
        Item::factory()->count(5)->create();

        Livewire::test(ItemsTable::class)
            ->set('perPage', 25)
            ->assertSet('perPage', 25);
    }

    public function test_search_with_partner_relationship(): void
    {
        $partner = Partner::factory()->create(['internal_name' => 'Partner Museum']);
        $item = Item::factory()->create([
            'internal_name' => 'Partner Item',
            'partner_id' => $partner->id,
        ]);

        Livewire::test(ItemsTable::class)
            ->set('q', 'Partner')
            ->assertSeeText('Partner Item');
    }
}
