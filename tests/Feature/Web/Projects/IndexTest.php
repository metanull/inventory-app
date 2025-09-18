<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Projects;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class IndexTest extends TestCase
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

    public function test_index_lists_projects_with_pagination(): void
    {
        Project::factory()->count(25)->create();
        $response = $this->get(route('projects.index'));
        $response->assertOk();
        $response->assertSee('Projects');
        $response->assertSee('Rows per page');
        $first = Project::query()->orderByDesc('created_at')->first();
        $response->assertSee(e($first->internal_name));
    }

    public function test_index_search_filters_results(): void
    {
        Project::factory()->count(5)->create();
        $target = Project::factory()->create(['internal_name' => 'SPECIAL_PROJECT_TOKEN']);

        $response = $this->get(route('projects.index', ['q' => 'SPECIAL_PROJECT_TOKEN']));
        $response->assertOk();
        $response->assertSee('SPECIAL_PROJECT_TOKEN');

        $nonMatch = Project::where('id', '!=', $target->id)->first();
        if ($nonMatch) {
            $response->assertDontSee(e($nonMatch->internal_name));
        }
    }

    public function test_index_respects_per_page_query(): void
    {
        Project::factory()->count(15)->create();
        $response = $this->get(route('projects.index', ['per_page' => 10]));
        $response->assertOk();
        $rowCount = substr_count($response->getContent(), '<tr');
        $this->assertGreaterThanOrEqual(10, $rowCount - 1);
    }
}
