<?php

namespace Tests\Feature\Api\Project;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class StoreTest extends TestCase
{
    use CreatesUsersWithPermissions, RefreshDatabase, WithFaker;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createDataUser();
        $this->actingAs($this->user);
    }

    public function test_store_allows_authenticated_users(): void
    {
        $data = Project::factory()->make()->except(['id']);
        $response = $this->postJson(route('project.store'), $data);
        $response->assertCreated();
    }

    public function test_store_creates_a_row(): void
    {
        $data = Project::factory()->make()->except(['id']);
        $response = $this->postJson(route('project.store'), $data);
        $response->assertCreated();
        $this->assertDatabaseHas('projects', ['internal_name' => $data['internal_name']]);
    }

    public function test_store_returns_created_on_success(): void
    {
        $data = Project::factory()->make()->except(['id']);
        $response = $this->postJson(route('project.store'), $data);
        $response->assertCreated();
    }

    public function test_store_returns_unprocessable_entity_when_input_is_invalid(): void
    {
        $data = ['internal_name' => '']; // Invalid: empty required field
        $response = $this->postJson(route('project.store'), $data);
        $response->assertUnprocessable();
    }

    public function test_store_returns_the_expected_structure(): void
    {
        $data = Project::factory()->make()->except(['id']);
        $response = $this->postJson(route('project.store', ['include' => 'context,language']), $data);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'internal_name',
                'backward_compatibility',
                'launch_date',
                'is_launched',
                'is_enabled',
                'context',
                'language',
                'created_at',
                'updated_at',
            ],
        ]);
    }

    public function test_store_returns_the_expected_data(): void
    {
        $data = Project::factory()->make()->except(['id']);
        $response = $this->postJson(route('project.store', ['include' => 'context,language']), $data);
        $response->assertCreated();
        $response->assertJsonPath('data.internal_name', $data['internal_name']);
        $this->assertDatabaseHas('projects', ['internal_name' => $data['internal_name']]);
    }

    public function test_store_validates_its_input(): void
    {
        $invalidData = ['internal_name' => '']; // Required field empty
        $response = $this->postJson(route('project.store'), $invalidData);
        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['internal_name']);
    }
}
