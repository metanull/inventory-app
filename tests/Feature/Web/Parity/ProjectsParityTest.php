<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Parity;

use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\RequiresDataPermissions;

class ProjectsParityTest extends TestCase
{
    use RefreshDatabase;
    use RequiresDataPermissions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actAsRegularUser();
    }

    public function test_api_meta_total_matches_web_count_first_page(): void
    {
        Project::factory()->count(31)->create();

        // API index with per_page=25
        $api = $this->getJson(route('project.index', ['per_page' => 25]));
        $api->assertOk();
        $apiTotal = $api->json('meta.total');
        $this->assertSame(31, $apiTotal);

        // Web page with perPage=25 should show 25 <tr> rows (plus header)
        $web = $this->get(route('projects.index', ['perPage' => 25]));
        $web->assertOk();
        $rowCount = substr_count($web->getContent(), '<tr');
        $this->assertGreaterThanOrEqual(25, $rowCount - 1);
    }
}
