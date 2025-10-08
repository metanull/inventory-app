<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Contexts;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\RequiresDataPermissions;

class CreateTest extends TestCase
{
    use RefreshDatabase;
    use RequiresDataPermissions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actAsRegularUser();
    }

    public function test_create_form_renders(): void
    {
        $response = $this->get(route('contexts.create'));
        $response->assertOk();
        $response->assertSee('Create Context');
        $response->assertSee('Internal Name');
    }
}
