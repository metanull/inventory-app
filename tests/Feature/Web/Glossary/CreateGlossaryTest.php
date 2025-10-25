<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Glossary;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\RequiresDataPermissions;

class CreateGlossaryTest extends TestCase
{
    use RefreshDatabase;
    use RequiresDataPermissions;

    public function test_create_displays_form(): void
    {
        $user = $this->createAuthenticatedUserWithDataPermissions();
        $this->actingAs($user);

        $response = $this->get(route('glossaries.create'));

        $response->assertOk();
        $response->assertViewIs('glossary.create');
        $response->assertSee('Internal Name');
        $response->assertSee('Legacy ID');
        // Verify form has the correct action and method
        $response->assertSee('action="'.route('glossaries.store').'"', false);
        $response->assertSee('name="internal_name"', false);
        $response->assertSee('name="backward_compatibility"', false);
    }

    public function test_create_requires_authentication(): void
    {
        $response = $this->get(route('glossaries.create'));

        $response->assertRedirect(route('login'));
    }

    public function test_create_requires_create_data_permission(): void
    {
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('glossaries.create'));

        $response->assertForbidden();
    }
}
