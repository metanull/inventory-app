<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Tags;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\RequiresDataPermissions;

class CreateTagTest extends TestCase
{
    use RefreshDatabase;
    use RequiresDataPermissions;

    public function test_create_displays_form(): void
    {
        $user = $this->createAuthenticatedUserWithDataPermissions();
        $this->actingAs($user);

        $response = $this->get(route('tags.create'));

        $response->assertOk();
        $response->assertViewIs('tags.create');
    }

    public function test_create_requires_authentication(): void
    {
        $response = $this->get(route('tags.create'));

        $response->assertRedirect(route('login'));
    }

    public function test_create_requires_create_data_permission(): void
    {
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('tags.create'));

        $response->assertForbidden();
    }
}
