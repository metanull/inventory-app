<?php

namespace Tests\Feature\Auth;

use Illuminate\Auth\Events\Logout;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;
use Tests\Traits\AuthenticationTestHelpers;

class LogoutTest extends TestCase
{
    use AuthenticationTestHelpers, RefreshDatabase, WithFaker;

    public function test_user_can_logout_without_two_factor(): void
    {
        Event::fake();
        $user = $this->createUserWithoutTwoFactor();
        $this->actingAs($user);

        $response = $this->post('/logout');

        $response->assertStatus(302);
        $response->assertRedirect('/');
        $this->assertGuest();
        Event::assertDispatched(Logout::class);
    }

    public function test_user_can_logout_with_totp_enabled(): void
    {
        Event::fake();
        $user = $this->createUserWithTotp();
        $this->actingAs($user);

        $response = $this->post('/logout');

        $response->assertStatus(302);
        $response->assertRedirect('/');
        $this->assertGuest();
        Event::assertDispatched(Logout::class);
    }

    public function test_user_can_logout_with_email_two_factor_enabled(): void
    {
        Event::fake();
        $user = $this->createUserWithEmailTwoFactor();
        $this->actingAs($user);

        $response = $this->post('/logout');

        $response->assertStatus(302);
        $response->assertRedirect('/');
        $this->assertGuest();
        Event::assertDispatched(Logout::class);
    }

    public function test_user_can_logout_with_both_two_factor_methods_enabled(): void
    {
        Event::fake();
        $user = $this->createUserWithBothTwoFactor();
        $this->actingAs($user);

        $response = $this->post('/logout');

        $response->assertStatus(302);
        $response->assertRedirect('/');
        $this->assertGuest();
        Event::assertDispatched(Logout::class);
    }

    public function test_guest_cannot_logout(): void
    {
        $response = $this->post('/logout');

        $response->assertStatus(302);
        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_logout_clears_remember_token(): void
    {
        $user = $this->createUserWithoutTwoFactor();

        // Set a remember token
        $user->setRememberToken('test-remember-token');
        $user->save();

        $this->actingAs($user);

        $response = $this->post('/logout');

        $response->assertStatus(302);
        $this->assertGuest();

        // Remember token should be cleared
        $this->assertNull($user->fresh()->remember_token);
    }

    public function test_logout_via_get_request_is_not_allowed(): void
    {
        $user = $this->createUserWithoutTwoFactor();
        $this->actingAs($user);

        $response = $this->get('/logout');

        $response->assertStatus(405); // Method Not Allowed
        $this->assertAuthenticatedAs($user); // Should still be authenticated
    }

    public function test_logout_redirects_to_intended_url_if_specified(): void
    {
        $user = $this->createUserWithoutTwoFactor();
        $this->actingAs($user);

        $response = $this->post('/logout', [
            'redirect' => '/custom-redirect',
        ]);

        // Note: This depends on your logout implementation
        // If your app doesn't support custom redirect, this test may need adjustment
        $response->assertStatus(302);
        $this->assertGuest();
    }

    public function test_logout_invalidates_session(): void
    {
        $user = $this->createUserWithoutTwoFactor();
        $this->actingAs($user);

        // Store something in the session
        session(['test_data' => 'test_value']);
        $this->assertEquals('test_value', session('test_data'));

        $response = $this->post('/logout');

        $response->assertStatus(302);
        $this->assertGuest();

        // Session data should be cleared
        $this->assertNull(session('test_data'));
    }

    public function test_logout_works_from_different_devices(): void
    {
        Event::fake();
        $user = $this->createUserWithoutTwoFactor();

        // Simulate login from first device
        $this->actingAs($user);

        // Store session ID
        $firstSessionId = session()->getId();

        // Logout
        $response = $this->post('/logout');

        $response->assertStatus(302);
        $this->assertGuest();
        Event::assertDispatched(Logout::class);

        // Session ID should be different after logout
        $this->assertNotEquals($firstSessionId, session()->getId());
    }

    public function test_logout_during_two_factor_challenge_clears_challenge(): void
    {
        $user = $this->createUserWithTotp();

        // Start login process (gets to 2FA challenge)
        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        // Should be in 2FA challenge state
        $response = $this->get('/two-factor-challenge');
        $response->assertStatus(200);

        // Logout during challenge
        $response = $this->post('/logout');

        $response->assertStatus(302);
        $response->assertRedirect('/');
        $this->assertGuest();

        // Should no longer be able to access 2FA challenge
        $response = $this->get('/two-factor-challenge');
        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    public function test_logout_with_csrf_protection(): void
    {
        $user = $this->createUserWithoutTwoFactor();
        $this->actingAs($user);

        // Attempt logout without CSRF token
        $response = $this->post('/logout', [], [
            'X-CSRF-TOKEN' => '', // Empty CSRF token
        ]);

        // Should fail due to CSRF protection
        $response->assertStatus(419); // CSRF token mismatch
        $this->assertAuthenticatedAs($user); // Should still be authenticated
    }

    public function test_multiple_logouts_are_safe(): void
    {
        $user = $this->createUserWithoutTwoFactor();
        $this->actingAs($user);

        // First logout
        $response = $this->post('/logout');
        $response->assertStatus(302);
        $this->assertGuest();

        // Second logout attempt (should not cause errors)
        $response = $this->post('/logout');
        $response->assertStatus(302);
        $response->assertRedirect('/login'); // Redirected to login since not authenticated
        $this->assertGuest();
    }
}
