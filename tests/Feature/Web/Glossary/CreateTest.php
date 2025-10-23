<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Glossary;

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

    public function test_create_displays_form(): void
    {
        $response = $this->get(route('glossaries.create'));
        $response->assertOk();
        $response->assertSee('Create Glossary');
        $response->assertSee('Internal Name');
    }
}
