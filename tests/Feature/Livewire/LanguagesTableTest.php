<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire;

use App\Livewire\Tables\LanguagesTable;
use App\Models\Language;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use Tests\TestCase;

class LanguagesTableTest extends TestCase
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
        Livewire::test(LanguagesTable::class)
            ->assertStatus(200);
    }

    public function test_search_filters_languages(): void
    {
        $language1 = Language::factory()->create(['internal_name' => 'English']);
        $language2 = Language::factory()->create(['internal_name' => 'French']);

        Livewire::test(LanguagesTable::class)
            ->set('q', 'English')
            ->assertSeeText('English')
            ->assertDontSeeText('French');
    }

    public function test_search_is_debounced(): void
    {
        Language::factory()->create(['internal_name' => 'Test Language']);

        Livewire::test(LanguagesTable::class)
            ->set('q', 'Test')
            ->assertSeeText('Test Language');
    }

    public function test_pagination_changes_page(): void
    {
        Language::factory()->count(50)->create();

        Livewire::test(LanguagesTable::class)
            ->set('perPage', 10)
            ->call('gotoPage', 2)
            ->assertSet('perPage', 10);
    }

    public function test_per_page_changes_limit(): void
    {
        Language::factory()->count(30)->create();

        Livewire::test(LanguagesTable::class)
            ->set('perPage', 20)
            ->assertSet('perPage', 20);
    }
}
