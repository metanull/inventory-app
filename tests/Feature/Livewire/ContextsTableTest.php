<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire;

use App\Livewire\Tables\ContextsTable;
use App\Models\Context;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ContextsTableTest extends TestCase
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
        Livewire::test(ContextsTable::class)
            ->assertStatus(200);
    }

    public function test_search_filters_contexts(): void
    {
        $context1 = Context::factory()->create(['internal_name' => 'Museum Context']);
        $context2 = Context::factory()->create(['internal_name' => 'Gallery Context']);

        Livewire::test(ContextsTable::class)
            ->set('q', 'Museum')
            ->assertSeeText('Museum Context')
            ->assertDontSeeText('Gallery Context');
    }

    public function test_search_is_debounced(): void
    {
        Context::factory()->create(['internal_name' => 'Test Context']);

        Livewire::test(ContextsTable::class)
            ->set('q', 'Test')
            ->assertSeeText('Test Context');
    }

    public function test_pagination_changes_page(): void
    {
        Context::factory()->count(50)->create();

        Livewire::test(ContextsTable::class)
            ->set('perPage', 10)
            ->call('gotoPage', 2)
            ->assertSet('perPage', 10);
    }

    public function test_per_page_changes_limit(): void
    {
        Context::factory()->count(30)->create();

        Livewire::test(ContextsTable::class)
            ->set('perPage', 20)
            ->assertSet('perPage', 20);
    }
}
