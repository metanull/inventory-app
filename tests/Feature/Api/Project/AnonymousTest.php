<?php

namespace Tests\Feature\Api\Project;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AnonymousTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_index_forbids_anonymous_access(): void
    {
        $response = $this->getJson(route('project.index'));
        $response->assertUnauthorized();
    }

    public function test_show_forbids_anonymous_access(): void
    {
        $project = Project::factory()->create();
        $response = $this->getJson(route('project.show', $project));
        $response->assertUnauthorized();
    }

    public function test_store_forbids_anonymous_access(): void
    {
        $data = Project::factory()->make()->toArray();
        $response = $this->postJson(route('project.store'), $data);
        $response->assertUnauthorized();
    }

    public function test_update_forbids_anonymous_access(): void
    {
        $project = Project::factory()->create();
        $data = ['internal_name' => 'Updated Name'];
        $response = $this->putJson(route('project.update', $project), $data);
        $response->assertUnauthorized();
    }

    public function test_destroy_forbids_anonymous_access(): void
    {
        $project = Project::factory()->create();
        $response = $this->deleteJson(route('project.destroy', $project));
        $response->assertUnauthorized();
    }
}
