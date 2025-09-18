<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Projects;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShowTest extends TestCase
{
    use RefreshDatabase;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_show_displays_core_fields(): void
    {
        $project = Project::factory()->create([
            'internal_name' => 'Alpha Project',
            'backward_compatibility' => 'LEG-PRJ',
            'is_enabled' => true,
        ]);

        $response = $this->get(route('projects.show', $project));
        $response->assertOk();
        $response->assertSee('Alpha Project');
        $response->assertSee('Legacy: LEG-PRJ');
        $response->assertSee('Information');
    }
}
