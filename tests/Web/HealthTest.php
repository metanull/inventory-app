<?php

namespace Tests\Feature\Health;

use App\Models\User;
use Tests\TestCase;

class HealthTest extends TestCase
{
    public function test_the_application_is_up_and_running_as_anonymous(): void
    {
        $response = $this->get('/');
        $response->assertRedirect('/web');

        // Follow the redirect and verify the final destination is accessible
        $response = $this->get('/web');
        $response->assertOk();
    }

    public function test_the_application_is_up_and_running_as_a_user(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get('/');
        $response->assertRedirect('/web');

        // Follow the redirect and verify the final destination is accessible
        $response = $this->actingAs($user)->get('/web');
        $response->assertOk();
    }
}
