<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Projects;

use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\RequiresDataPermissions;

class ShowTest extends TestCase
{
    use RefreshDatabase;
    use RequiresDataPermissions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actAsRegularUser();
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
