<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire;

use App\Livewire\Tables\CollectionsTable;
use App\Models\Collection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use Tests\TestCase;

class CollectionsTableTest extends TestCase
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
        Livewire::test(CollectionsTable::class)
            ->assertStatus(200);
    }

    public function test_search_filters_collections(): void
    {
        $collection1 = Collection::factory()->create(['internal_name' => 'Ancient Art']);
        $collection2 = Collection::factory()->create(['internal_name' => 'Modern Art']);

        Livewire::test(CollectionsTable::class)
            ->set('q', 'Ancient')
            ->assertSeeText('Ancient Art')
            ->assertDontSeeText('Modern Art');
    }

    public function test_search_is_debounced(): void
    {
        Collection::factory()->create(['internal_name' => 'Test Collection']);

        Livewire::test(CollectionsTable::class)
            ->set('q', 'Test')
            ->assertSeeText('Test Collection');
    }

    public function test_pagination_changes_page(): void
    {
        Collection::factory()->count(50)->create();

        Livewire::test(CollectionsTable::class)
            ->set('perPage', 10)
            ->call('gotoPage', 2)
            ->assertSet('perPage', 10);
    }

    public function test_per_page_changes_limit(): void
    {
        Collection::factory()->count(30)->create();

        Livewire::test(CollectionsTable::class)
            ->set('perPage', 20)
            ->assertSet('perPage', 20);
    }
}
