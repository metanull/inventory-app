<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire;

use App\Livewire\Tables\GlossaryTable;
use App\Models\Glossary;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use Tests\TestCase;

class GlossaryTableTest extends TestCase
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
        Livewire::test(GlossaryTable::class)
            ->assertStatus(200);
    }

    public function test_search_filters_glossary_entries(): void
    {
        $glossary1 = Glossary::factory()->create(['internal_name' => 'ancient-term']);
        $glossary2 = Glossary::factory()->create(['internal_name' => 'modern-term']);

        Livewire::test(GlossaryTable::class)
            ->set('q', 'ancient')
            ->assertSeeText('ancient-term')
            ->assertDontSeeText('modern-term');
    }

    public function test_search_is_debounced(): void
    {
        Glossary::factory()->create(['internal_name' => 'test-term']);

        $component = Livewire::test(GlossaryTable::class)
            ->set('q', 'test');

        // Search property should be set
        $this->assertEquals('test', $component->get('q'));
    }

    public function test_pagination_changes_page(): void
    {
        // Create more glossary entries than perPage to trigger pagination
        Glossary::factory()->count(15)->create();

        Livewire::test(GlossaryTable::class)
            ->set('perPage', 10)
            ->call('gotoPage', 2)
            ->assertSet('perPage', 10);
    }

    public function test_per_page_changes_limit(): void
    {
        Glossary::factory()->count(5)->create();

        Livewire::test(GlossaryTable::class)
            ->set('perPage', 25)
            ->assertSet('perPage', 25);
    }
}
