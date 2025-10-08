<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Projects;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\RequiresDataPermissions;

class CreateTest extends TestCase
{
    use RefreshDatabase;
    use RequiresDataPermissions;
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actAsRegularUser();
    }

    public function test_create_form_renders(): void
    {
        $response = $this->get(route('projects.create'));
        $response->assertOk();
        $response->assertSee('Create Project');
        $response->assertSee('Internal Name');
    }
}
