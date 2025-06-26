<?php

namespace Tests\Unit\Project;

use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class FactoryTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_factory(): void
    {
        $project = Project::factory()->make();
        $this->assertInstanceOf(Project::class, $project);
        $this->assertNotNull($project->id);
        $this->assertNotNull($project->internal_name);
        $this->assertNotNull($project->backward_compatibility);
        $this->assertNull($project->launch_date);
        $this->assertFalse($project->is_launched);
        $this->assertFalse($project->is_enabled);
        $this->assertNull($project->context_id);
        $this->assertNull($project->language_id);
    }

    public function test_factory_with_enabled(): void
    {
        $project = Project::factory()->withEnabled()->make();
        $this->assertInstanceOf(Project::class, $project);
        $this->assertTrue($project->is_enabled);
    }

    public function test_factory_with_launched(): void
    {
        $project = Project::factory()->withLaunched()->make();
        $this->assertInstanceOf(Project::class, $project);
        $this->assertTrue($project->is_launched);
        $this->assertNotNull($project->launch_date);
    }

    public function test_factory_with_context(): void
    {
        $project = Project::factory()->withContext()->make();
        $this->assertInstanceOf(Project::class, $project);
        $this->assertNotNull($project->context_id);
    }

    public function test_factory_with_language(): void
    {
        $project = Project::factory()->withLanguage()->make();
        $this->assertInstanceOf(Project::class, $project);
        $this->assertNotNull($project->language_id);
    }

    public function test_factory_creates_a_row_in_database(): void
    {
        $project = Project::factory()->create();
        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'internal_name' => $project->internal_name,
            'backward_compatibility' => $project->backward_compatibility,
            'launch_date' => null,
            'is_launched' => false,
            'is_enabled' => false,
            'context_id' => null,
            'language_id' => null,
        ]);
        $this->assertDatabaseCount('projects', 1);
    }

    public function test_factory_creates_a_row_in_database_with_enabled(): void
    {
        $project = Project::factory()->withEnabled()->create();
        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'is_enabled' => true,
        ]);
        $this->assertDatabaseCount('projects', 1);
    }

    public function test_factory_creates_a_row_in_database_with_launched(): void
    {
        $project = Project::factory()->withLaunched()->create();
        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'is_launched' => true,
        ]);
        $this->assertNotNull($project->launch_date);
        $this->assertDatabaseCount('projects', 1);
    }

    public function test_factory_creates_a_row_in_database_with_context(): void
    {
        $project = Project::factory()->withContext()->create();
        $this->assertNotNull($project->context_id);
        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'context_id' => $project->context_id,
        ]);
        $this->assertDatabaseCount('projects', 1);
    }

    public function test_factory_creates_a_row_in_database_with_language(): void
    {
        $project = Project::factory()->withLanguage()->create();
        $this->assertNotNull($project->language_id);
        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'language_id' => $project->language_id,
        ]);
        $this->assertDatabaseCount('projects', 1);
    }
}
