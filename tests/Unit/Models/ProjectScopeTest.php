<?php

namespace Tests\Unit\Models;

use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests for Project model scopes.
 */
class ProjectScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_scope_is_enabled_returns_only_enabled_projects(): void
    {
        $enabled1 = Project::factory()->withEnabled()->create();
        $enabled2 = Project::factory()->withEnabled()->create();
        $disabled1 = Project::factory()->create(['is_enabled' => false]);
        $disabled2 = Project::factory()->create(['is_enabled' => false]);

        $results = Project::isEnabled()->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->contains('id', $enabled1->id));
        $this->assertTrue($results->contains('id', $enabled2->id));
        $this->assertFalse($results->contains('id', $disabled1->id));
        $this->assertFalse($results->contains('id', $disabled2->id));
    }

    public function test_scope_is_launched_returns_only_launched_projects(): void
    {
        $launched1 = Project::factory()->withLaunched()->create();
        $launched2 = Project::factory()->withLaunched()->create();
        $notLaunched1 = Project::factory()->create(['is_launched' => false]);
        $notLaunched2 = Project::factory()->create(['is_launched' => false]);

        $results = Project::isLaunched()->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->contains('id', $launched1->id));
        $this->assertTrue($results->contains('id', $launched2->id));
        $this->assertFalse($results->contains('id', $notLaunched1->id));
        $this->assertFalse($results->contains('id', $notLaunched2->id));
    }

    public function test_scope_is_launch_date_passed_returns_projects_with_past_dates(): void
    {
        $pastDate1 = Project::factory()->create([
            'launch_date' => now()->subDays(5),
        ]);
        $pastDate2 = Project::factory()->create([
            'launch_date' => now()->subDays(1),
        ]);
        $futureDate = Project::factory()->create([
            'launch_date' => now()->addDays(5),
        ]);
        $noDate = Project::factory()->create([
            'launch_date' => null,
        ]);

        $results = Project::isLaunchDatePassed()->get();

        $this->assertTrue($results->contains('id', $pastDate1->id));
        $this->assertTrue($results->contains('id', $pastDate2->id));
        $this->assertFalse($results->contains('id', $futureDate->id));
        $this->assertFalse($results->contains('id', $noDate->id));
    }

    public function test_scope_visible_returns_projects_that_are_enabled_launched_and_past_launch_date(): void
    {
        $visible1 = Project::factory()->create([
            'is_enabled' => true,
            'is_launched' => true,
            'launch_date' => now()->subDays(5),
        ]);
        $visible2 = Project::factory()->create([
            'is_enabled' => true,
            'is_launched' => true,
            'launch_date' => now()->subDays(1),
        ]);

        // Not visible: not enabled
        $notEnabled = Project::factory()->create([
            'is_enabled' => false,
            'is_launched' => true,
            'launch_date' => now()->subDays(1),
        ]);

        // Not visible: not launched
        $notLaunched = Project::factory()->create([
            'is_enabled' => true,
            'is_launched' => false,
            'launch_date' => now()->subDays(1),
        ]);

        // Not visible: future launch date
        $futureDate = Project::factory()->create([
            'is_enabled' => true,
            'is_launched' => true,
            'launch_date' => now()->addDays(5),
        ]);

        $results = Project::visible()->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->contains('id', $visible1->id));
        $this->assertTrue($results->contains('id', $visible2->id));
        $this->assertFalse($results->contains('id', $notEnabled->id));
        $this->assertFalse($results->contains('id', $notLaunched->id));
        $this->assertFalse($results->contains('id', $futureDate->id));
    }

    public function test_scope_enabled_returns_projects_that_are_enabled_and_launched(): void
    {
        $enabled1 = Project::factory()->create([
            'is_enabled' => true,
            'is_launched' => true,
        ]);
        $enabled2 = Project::factory()->create([
            'is_enabled' => true,
            'is_launched' => true,
        ]);

        $notEnabled = Project::factory()->create([
            'is_enabled' => false,
            'is_launched' => true,
        ]);

        $notLaunched = Project::factory()->create([
            'is_enabled' => true,
            'is_launched' => false,
        ]);

        $results = Project::enabled()->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->contains('id', $enabled1->id));
        $this->assertTrue($results->contains('id', $enabled2->id));
        $this->assertFalse($results->contains('id', $notEnabled->id));
        $this->assertFalse($results->contains('id', $notLaunched->id));
    }
}
