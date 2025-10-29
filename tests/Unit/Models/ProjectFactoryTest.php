<?php

namespace Tests\Unit\Models;

use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests for Project factory states and methods.
 */
class ProjectFactoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_factory_creates_valid_project(): void
    {
        $project = Project::factory()->create();

        $this->assertInstanceOf(Project::class, $project);
        $this->assertNotEmpty($project->id);
        $this->assertNotEmpty($project->internal_name);
        $this->assertFalse($project->is_enabled);
        $this->assertFalse($project->is_launched);
    }

    public function test_factory_with_enabled_sets_enabled_flag(): void
    {
        $project = Project::factory()->withEnabled()->create();

        $this->assertTrue($project->is_enabled);
    }

    public function test_factory_with_launched_sets_launched_state(): void
    {
        $project = Project::factory()->withLaunched()->create();

        $this->assertTrue($project->is_launched);
        $this->assertNotNull($project->launch_date);
    }

    public function test_factory_with_context_creates_context_relationship(): void
    {
        $project = Project::factory()->withContext()->create();

        $this->assertNotNull($project->context_id);
        $this->assertInstanceOf(\App\Models\Context::class, $project->context);
    }

    public function test_factory_with_language_creates_language_relationship(): void
    {
        $project = Project::factory()->withLanguage()->create();

        $this->assertNotNull($project->language_id);
        $this->assertInstanceOf(\App\Models\Language::class, $project->language);
    }
}
