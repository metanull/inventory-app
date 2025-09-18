<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire;

use App\Livewire\Tables\ProjectsTable;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use Tests\TestCase;

class ProjectsTableTest extends TestCase
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
        Livewire::test(ProjectsTable::class)
            ->assertStatus(200);
    }

    public function test_search_filters_projects(): void
    {
        $project1 = Project::factory()->create(['internal_name' => 'Digital Archive']);
        $project2 = Project::factory()->create(['internal_name' => 'Physical Exhibition']);

        Livewire::test(ProjectsTable::class)
            ->set('q', 'Digital')
            ->assertSeeText('Digital Archive')
            ->assertDontSeeText('Physical Exhibition');
    }

    public function test_search_is_debounced(): void
    {
        Project::factory()->create(['internal_name' => 'Test Project']);

        Livewire::test(ProjectsTable::class)
            ->set('q', 'Test')
            ->assertSeeText('Test Project');
    }

    public function test_pagination_changes_page(): void
    {
        Project::factory()->count(50)->create();

        Livewire::test(ProjectsTable::class)
            ->set('perPage', 10)
            ->call('gotoPage', 2)
            ->assertSet('perPage', 10);
    }

    public function test_per_page_changes_limit(): void
    {
        Project::factory()->count(30)->create();

        Livewire::test(ProjectsTable::class)
            ->set('perPage', 20)
            ->assertSet('perPage', 20);
    }
}
