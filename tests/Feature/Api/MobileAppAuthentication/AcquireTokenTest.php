<?php

namespace Tests\Feature\Api\MobileAppAuthentication;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AcquireTokenTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_can_acquire_token_with_valid_credentials(): void
    {
        $response = $this->postJson(route('token.acquire'), [
            'email' => $this->user->email,
            'password' => 'password',
            'device_name' => 'Test Device',
        ]);

        $response->assertCreated();
        $this->assertNotEmpty($response->getContent());
    }

    public function test_can_acquire_token_without_wipe_tokens_parameter(): void
    {
        $response = $this->postJson(route('token.acquire'), [
            'email' => $this->user->email,
            'password' => 'password',
            'device_name' => 'Test Device',
        ]);

        $response->assertCreated();
        $this->assertCount(1, $this->user->fresh()->tokens);
    }

    public function test_can_acquire_token_with_wipe_tokens_false(): void
    {
        // Create an existing token
        $this->user->createToken('Existing Token');

        $response = $this->postJson(route('token.acquire'), [
            'email' => $this->user->email,
            'password' => 'password',
            'device_name' => 'Test Device',
            'wipe_tokens' => false,
        ]);

        $response->assertCreated();
        $this->assertCount(2, $this->user->fresh()->tokens);
    }

    public function test_can_acquire_token_with_wipe_tokens_true(): void
    {
        // Create an existing token
        $this->user->createToken('Existing Token');

        $response = $this->postJson(route('token.acquire'), [
            'email' => $this->user->email,
            'password' => 'password',
            'device_name' => 'Test Device',
            'wipe_tokens' => true,
        ]);

        $response->assertCreated();
        $this->assertCount(1, $this->user->fresh()->tokens);
    }

    public function test_cannot_acquire_token_with_invalid_email(): void
    {
        $response = $this->postJson(route('token.acquire'), [
            'email' => 'nonexistent@example.com',
            'password' => 'password',
            'device_name' => 'Test Device',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_cannot_acquire_token_with_invalid_password(): void
    {
        $response = $this->postJson(route('token.acquire'), [
            'email' => $this->user->email,
            'password' => 'wrong-password',
            'device_name' => 'Test Device',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_cannot_acquire_token_without_email(): void
    {
        $response = $this->postJson(route('token.acquire'), [
            'password' => 'password',
            'device_name' => 'Test Device',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_cannot_acquire_token_without_password(): void
    {
        $response = $this->postJson(route('token.acquire'), [
            'email' => $this->user->email,
            'device_name' => 'Test Device',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
    }

    public function test_cannot_acquire_token_without_device_name(): void
    {
        $response = $this->postJson(route('token.acquire'), [
            'email' => $this->user->email,
            'password' => 'password',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['device_name']);
    }

    public function test_cannot_acquire_token_with_invalid_email_format(): void
    {
        $response = $this->postJson(route('token.acquire'), [
            'email' => 'not-an-email',
            'password' => 'password',
            'device_name' => 'Test Device',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_cannot_acquire_token_with_long_device_name(): void
    {
        $response = $this->postJson(route('token.acquire'), [
            'email' => $this->user->email,
            'password' => 'password',
            'device_name' => str_repeat('A', 256),
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['device_name']);
    }

    public function test_cannot_acquire_token_with_invalid_wipe_tokens_value(): void
    {
        $response = $this->postJson(route('token.acquire'), [
            'email' => $this->user->email,
            'password' => 'password',
            'device_name' => 'Test Device',
            'wipe_tokens' => 'not-a-boolean',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['wipe_tokens']);
    }
}
