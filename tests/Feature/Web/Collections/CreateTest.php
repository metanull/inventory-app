<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Collections;

use App\Models\Context;
use App\Models\Language;
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
        // Ensure dropdowns have data
        Context::factory()->create();
        Language::factory()->create();

        $response = $this->get(route('collections.create'));
        $response->assertOk();
        $response->assertSee('Create Collection');
        $response->assertSee('Internal Name');
    }
}
