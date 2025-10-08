<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Countries;

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
        $response = $this->get(route('countries.create'));
        $response->assertOk();
        $response->assertSee('Create Country');
        $response->assertSee('Code (3 letters)');
        $response->assertSee('Internal Name');
    }
}
