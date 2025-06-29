<?php

namespace Tests\Feature\Api\MobileAppAuthentication;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AnonymousTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_acquire_token_allows_anonymous_access(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson(route('token.acquire'), [
            'email' => $user->email,
            'password' => 'password',
            'device_name' => 'Test Device',
        ]);

        $response->assertCreated();
    }

    public function test_wipe_tokens_requires_authentication(): void
    {
        $response = $this->getJson(route('token.wipe'));

        $response->assertUnauthorized();
    }
}
