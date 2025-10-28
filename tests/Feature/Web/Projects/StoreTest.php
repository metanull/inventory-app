<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Projects;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class StoreTest extends TestCase
{
    use CreatesUsersWithPermissions;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsDataUser();
    }

    public function test_store_persists_project_and_redirects(): void
    {
        $payload = [
            'internal_name' => 'Test Project',
            'backward_compatibility' => 'PRJ-LEG',
            'is_launched' => false,
            'is_enabled' => true,
        ];

        $response = $this->post(route('projects.store'), $payload);
        $response->assertRedirect();
        $this->assertDatabaseHas('projects', [
            'internal_name' => 'Test Project',
            'is_enabled' => true,
        ]);
    }

    public function test_store_validation_errors(): void
    {
        $response = $this->post(route('projects.store'), [
            'internal_name' => '',
            'is_enabled' => 'not-boolean',
        ]);
        $response->assertSessionHasErrors(['internal_name', 'is_enabled']);
    }
}
