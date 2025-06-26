<?php

namespace Tests\Feature\Api\Project;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DestroyTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_destroy_allows_authenticated_users(): void
    {
        $project = Project::factory()->create();
        $response = $this->deleteJson(route('project.destroy', $project));
        $response->assertNoContent();
    }

    public function test_destroy_deletes_a_row(): void
    {
        $project = Project::factory()->create();
        $response = $this->deleteJson(route('project.destroy', $project));
        $response->assertNoContent();
        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
    }

    public function test_destroy_returns_no_content_on_success(): void
    {
        $project = Project::factory()->create();
        $response = $this->deleteJson(route('project.destroy', $project));
        $response->assertNoContent();
    }

    public function test_destroy_returns_not_found_when_record_does_not_exist(): void
    {
        $response = $this->deleteJson(route('project.destroy', 'non-existent-id'));
        $response->assertNotFound();
    }

    public function test_destroy_returns_the_expected_structure(): void
    {
        $project = Project::factory()->create();
        $response = $this->deleteJson(route('project.destroy', $project));
        $response->assertNoContent();
        $this->assertEmpty($response->getContent());
    }

    public function test_destroy_returns_the_expected_data(): void
    {
        $project = Project::factory()->create();
        $response = $this->deleteJson(route('project.destroy', $project));
        $response->assertNoContent();
        $this->assertEmpty($response->getContent());
        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
    }
}
