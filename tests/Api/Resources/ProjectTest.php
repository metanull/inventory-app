<?php

namespace Tests\Api\Resources;

use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Api\Traits\AuthenticatesApiRequests;
use Tests\Api\Traits\TestsApiCrud;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    use AuthenticatesApiRequests;
    use RefreshDatabase;
    use TestsApiCrud;

    protected function getResourceName(): string
    {
        return 'project';
    }

    protected function getModelClass(): string
    {
        return Project::class;
    }

    public function test_resource_includes_url_fields(): void
    {
        $project = Project::factory()->withUrls()->create();

        $response = $this->getJson(route('project.show', $project));

        $response->assertOk()
            ->assertJsonPath('data.site_url', $project->site_url)
            ->assertJsonPath('data.related_database_url', $project->related_database_url)
            ->assertJsonPath('data.artistic_introduction_url', $project->artistic_introduction_url);
    }

    public function test_can_create_project_with_url_fields(): void
    {
        $data = Project::factory()->make([
            'site_url' => 'https://example.org/project',
            'related_database_url' => 'https://example.org/database',
            'artistic_introduction_url' => 'https://example.org/intro',
        ])->toArray();
        $data = array_diff_key($data, array_flip(['id', 'created_at', 'updated_at']));

        $response = $this->postJson(route('project.store'), $data);

        $response->assertCreated()
            ->assertJsonPath('data.site_url', 'https://example.org/project')
            ->assertJsonPath('data.related_database_url', 'https://example.org/database')
            ->assertJsonPath('data.artistic_introduction_url', 'https://example.org/intro');

        $this->assertDatabaseHas('projects', [
            'id' => $response->json('data.id'),
            'site_url' => 'https://example.org/project',
            'related_database_url' => 'https://example.org/database',
            'artistic_introduction_url' => 'https://example.org/intro',
        ]);
    }

    public function test_can_update_project_with_url_fields(): void
    {
        $project = Project::factory()->create();
        $data = Project::factory()->make([
            'site_url' => 'https://example.org/updated-site',
            'related_database_url' => 'https://example.org/updated-database',
            'artistic_introduction_url' => 'https://example.org/updated-intro',
        ])->toArray();
        $data = array_diff_key($data, array_flip(['id', 'created_at', 'updated_at']));

        $response = $this->putJson(route('project.update', $project), $data);

        $response->assertOk()
            ->assertJsonPath('data.site_url', 'https://example.org/updated-site')
            ->assertJsonPath('data.related_database_url', 'https://example.org/updated-database')
            ->assertJsonPath('data.artistic_introduction_url', 'https://example.org/updated-intro');

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'site_url' => 'https://example.org/updated-site',
            'related_database_url' => 'https://example.org/updated-database',
            'artistic_introduction_url' => 'https://example.org/updated-intro',
        ]);
    }

    public function test_create_project_fails_with_invalid_site_url(): void
    {
        $data = Project::factory()->make(['site_url' => 'not a url'])->toArray();
        $data = array_diff_key($data, array_flip(['id', 'created_at', 'updated_at']));

        $response = $this->postJson(route('project.store'), $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['site_url']);
    }

    public function test_create_project_fails_with_invalid_related_database_url(): void
    {
        $data = Project::factory()->make(['related_database_url' => 'not a url'])->toArray();
        $data = array_diff_key($data, array_flip(['id', 'created_at', 'updated_at']));

        $response = $this->postJson(route('project.store'), $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['related_database_url']);
    }

    public function test_create_project_fails_with_invalid_artistic_introduction_url(): void
    {
        $data = Project::factory()->make(['artistic_introduction_url' => 'not a url'])->toArray();
        $data = array_diff_key($data, array_flip(['id', 'created_at', 'updated_at']));

        $response = $this->postJson(route('project.store'), $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['artistic_introduction_url']);
    }

    public function test_update_project_fails_with_invalid_site_url(): void
    {
        $project = Project::factory()->create();
        $data = Project::factory()->make(['site_url' => 'not a url'])->toArray();
        $data = array_diff_key($data, array_flip(['id', 'created_at', 'updated_at']));

        $response = $this->putJson(route('project.update', $project), $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['site_url']);
    }
}
