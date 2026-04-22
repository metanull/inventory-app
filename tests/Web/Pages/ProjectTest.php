<?php

namespace Tests\Web\Pages;

use App\Models\Context;
use App\Models\Language;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Web\Traits\AuthenticatesWebRequests;
use Tests\Web\Traits\TestsWebCrud;

class ProjectTest extends TestCase
{
    use AuthenticatesWebRequests;
    use RefreshDatabase;
    use TestsWebCrud;

    protected function getRouteName(): string
    {
        return 'projects';
    }

    protected function getModelClass(): string
    {
        return Project::class;
    }

    protected function getFormData(): array
    {
        return Project::factory()->make()->toArray();
    }

    public function test_edit_page_passes_contexts_and_languages_from_controller(): void
    {
        Context::factory()->count(2)->create();
        Language::factory()->count(2)->create();
        $project = Project::factory()->create();

        $response = $this->get(route('projects.edit', $project));

        $response->assertOk()
            ->assertViewHas('contexts')
            ->assertViewHas('languages');
    }

    public function test_create_page_passes_contexts_and_languages_from_controller(): void
    {
        Context::factory()->count(2)->create();
        Language::factory()->count(2)->create();

        $response = $this->get(route('projects.create'));

        $response->assertOk()
            ->assertViewHas('contexts')
            ->assertViewHas('languages');
    }
}
