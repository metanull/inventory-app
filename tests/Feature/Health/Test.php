<?php

namespace Tests\Feature\Health;

use App\Models\User;
use Tests\TestCase;

class Test extends TestCase
{
    public function test_the_application_is_up_and_running_as_anonymous(): void
    {
        $response = $this->getJson('/');
        $response->assertStatus(200);
    }

    public function test_the_application_is_up_and_running_as_a_user(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->getJson('/');
        $response->assertStatus(200);
    }
}
