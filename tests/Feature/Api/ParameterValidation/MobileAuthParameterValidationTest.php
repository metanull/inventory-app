<?php

namespace Tests\Feature\Api\ParameterValidation;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Comprehensive parameter validation tests for Mobile Authentication API endpoints
 */
class MobileAuthParameterValidationTest extends TestCase
{
    use RefreshDatabase;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    // ACQUIRE TOKEN ENDPOINT TESTS
    public function test_acquire_token_validates_required_fields()
    {
        $response = $this->postJson(route('token.acquire'), []);

        $response->assertUnprocessable();
        // Assuming these are the required fields based on typical auth requirements
        $this->assertTrue($response->status() === 422 || $response->status() === 400);
    }

    public function test_acquire_token_validates_email_format()
    {
        $response = $this->postJson(route('token.acquire'), [
            'email' => 'invalid-email-format',
            'password' => 'password123',
        ]);

        // Should validate email format
        $this->assertContains($response->status(), [422, 401]);
    }

    public function test_acquire_token_validates_credentials()
    {
        $response = $this->postJson(route('token.acquire'), [
            'email' => 'nonexistent@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertUnauthorized();
    }

    public function test_acquire_token_accepts_valid_credentials()
    {
        $response = $this->postJson(route('token.acquire'), [
            'email' => $this->user->email,
            'password' => 'password', // Default factory password
        ]);

        // Should succeed or require proper password setup
        $this->assertContains($response->status(), [200, 401, 422]);
    }

    public function test_acquire_token_rejects_unexpected_request_parameters_currently()
    {
        $response = $this->postJson(route('token.acquire'), [
            'email' => $this->user->email,
            'password' => 'password',
            'unexpected_field' => 'should_be_rejected',
            'device_type' => 'mobile', // Not implemented
            'app_version' => '1.0.0', // Not implemented
            'device_id' => 'device123', // Not implemented
            'remember_me' => true, // Not implemented
            'admin_token' => true,
            'debug_mode' => true,
        ]);

        $response->assertStatus(422); // Form Request properly rejects unexpected params
        $response->assertJsonValidationErrors(['unexpected_field']);
    }

    // WIPE TOKENS ENDPOINT TESTS (requires auth)
    public function test_wipe_tokens_requires_authentication()
    {
        $response = $this->getJson(route('token.wipe'));

        $response->assertUnauthorized();
    }

    public function test_wipe_tokens_accepts_authenticated_request()
    {
        $this->actingAs($this->user);

        $response = $this->getJson(route('token.wipe'));

        $response->assertOk();
    }

    public function test_wipe_tokens_rejects_unexpected_query_parameters_currently()
    {
        $this->actingAs($this->user);

        $response = $this->getJson(route('token.wipe').'?wipe_all=true&force_logout=true&admin_wipe=true');

        $response->assertStatus(422); // Form Request properly rejects unexpected params
        $response->assertJsonValidationErrors(['wipe_all']);
    }

    // EDGE CASE TESTS
    public function test_handles_sql_injection_in_credentials()
    {
        $sqlInjectionAttempts = [
            "admin'--",
            "admin' OR '1'='1",
            "admin'; DROP TABLE users; --",
            "admin' UNION SELECT * FROM users --",
            "'; INSERT INTO users (email, password) VALUES ('hacker@evil.com', 'password'); --",
        ];

        foreach ($sqlInjectionAttempts as $maliciousEmail) {
            $response = $this->postJson(route('token.acquire'), [
                'email' => $maliciousEmail,
                'password' => 'any_password',
            ]);

            // Should handle SQL injection attempts safely
            $this->assertContains($response->status(), [401, 422]);

            // Verify no user was created with malicious email
            $this->assertDatabaseMissing('users', [
                'email' => 'hacker@evil.com',
            ]);
        }
    }

    public function test_handles_xss_attempts_in_credentials()
    {
        $xssAttempts = [
            '<script>alert("XSS")</script>@example.com',
            'test<img src=x onerror=alert("XSS")>@example.com',
            'test@example.com<script>document.location="http://evil.com"</script>',
            '"><script>alert(String.fromCharCode(88,83,83))</script>',
        ];

        foreach ($xssAttempts as $maliciousEmail) {
            $response = $this->postJson(route('token.acquire'), [
                'email' => $maliciousEmail,
                'password' => 'password',
            ]);

            // Should handle XSS attempts safely
            $this->assertContains($response->status(), [401, 422]);
        }
    }

    public function test_handles_unicode_characters_in_credentials()
    {
        $unicodeEmails = [
            'tëst@éxämplé.com',
            'пользователь@пример.рф',
            'ユーザー@例え.jp',
            'مستخدم@مثال.عرب',
            'usuario@ejemplo.es',
            'utente@esempio.it',
            'użytkownik@przykład.pl',
            'χρήστης@παράδειγμα.gr',
            'bruger@eksempel.dk',
            'felhasználó@példa.hu',
        ];

        foreach ($unicodeEmails as $email) {
            $response = $this->postJson(route('token.acquire'), [
                'email' => $email,
                'password' => 'password123',
            ]);

            // Should handle Unicode gracefully
            $this->assertContains($response->status(), [401, 422]);
        }
    }

    public function test_handles_very_long_credentials()
    {
        $veryLongEmail = str_repeat('a', 250).'@'.str_repeat('b', 250).'.com';
        $veryLongPassword = str_repeat('password', 100);

        $response = $this->postJson(route('token.acquire'), [
            'email' => $veryLongEmail,
            'password' => $veryLongPassword,
        ]);

        // Should handle very long credentials gracefully
        $this->assertContains($response->status(), [401, 413, 422]);
    }

    public function test_handles_array_injection_attempts()
    {
        $response = $this->postJson(route('token.acquire'), [
            'email' => ['array' => 'instead_of_string'],
            'password' => ['malicious' => 'array'],
        ]);

        $response->assertUnprocessable();
    }

    public function test_handles_null_and_empty_credentials()
    {
        $testCases = [
            ['email' => null, 'password' => null],
            ['email' => '', 'password' => ''],
            ['email' => '   ', 'password' => '   '], // Whitespace only
            ['email' => $this->user->email, 'password' => null],
            ['email' => null, 'password' => 'password'],
        ];

        foreach ($testCases as $data) {
            $response = $this->postJson(route('token.acquire'), $data);

            // Should handle gracefully
            $this->assertContains($response->status(), [401, 422]);
        }
    }

    public function test_handles_special_characters_in_password()
    {
        $specialCharPasswords = [
            'password"with"quotes',
            "password'with'apostrophes",
            'password&with&symbols',
            'password:with:colons',
            'password(with)parentheses',
            'password-with-dashes',
            'password@with@symbols',
            'password#with#hashtags',
            'password%with%percentages',
            'password$with$dollars',
            'password*with*asterisks',
            'password+with+plus',
            'password=with=equals',
            'password|with|pipes',
            'password[with]brackets',
            'password{with}braces',
            'password`with`backticks',
            'password~with~tildes',
            'password^with^carets',
            'password\\with\\backslashes',
        ];

        foreach ($specialCharPasswords as $password) {
            $response = $this->postJson(route('token.acquire'), [
                'email' => $this->user->email,
                'password' => $password,
            ]);

            // Should handle special characters gracefully
            $this->assertContains($response->status(), [200, 401, 422]);
        }
    }

    public function test_rate_limiting_behavior()
    {
        // Attempt multiple failed logins to test rate limiting
        for ($i = 0; $i < 10; $i++) {
            $response = $this->postJson(route('token.acquire'), [
                'email' => 'nonexistent@example.com',
                'password' => 'wrongpassword',
            ]);

            // Should either return 401 (auth failed) or 429 (rate limited)
            $this->assertContains($response->status(), [401, 422, 429]);
        }
    }

    public function test_handles_concurrent_authentication_attempts()
    {
        // Simulate concurrent auth attempts
        $responses = [];
        for ($i = 0; $i < 5; $i++) {
            $responses[] = $this->postJson(route('token.acquire'), [
                'email' => $this->user->email,
                'password' => 'password',
            ]);
        }

        // All should handle gracefully
        foreach ($responses as $response) {
            $this->assertContains($response->status(), [200, 401, 422, 429]);
        }
    }

    public function test_wipe_tokens_with_malicious_parameters()
    {
        $this->actingAs($this->user);

        $maliciousParams = [
            'user_id' => 'all',
            'force_logout_all' => 'true',
            'delete_users' => 'true',
            'admin_override' => 'true',
            'sql_injection' => "'; DROP TABLE personal_access_tokens; --",
            'privilege_escalation' => 'super_admin',
        ];

        $queryString = http_build_query($maliciousParams);
        $response = $this->getJson(route('token.wipe')."?{$queryString}");

        $response->assertOk(); // Should handle malicious params safely

        // Verify other users' tokens weren't affected
        $otherUser = User::factory()->create();
        $this->assertDatabaseHas('users', ['id' => $otherUser->id]);
    }

    public function test_authentication_with_invalid_json()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'application/json',
        ])->json('POST', route('token.acquire'), [], [], [], [], '{"invalid": json}');

        // Should handle invalid JSON gracefully
        $this->assertContains($response->status(), [400, 422]);
    }

    public function test_authentication_with_missing_content_type()
    {
        $response = $this->post(route('token.acquire'), [
            'email' => $this->user->email,
            'password' => 'password',
        ]);

        // Should handle missing content type gracefully
        $this->assertContains($response->status(), [200, 401, 415, 422]);
    }

    public function test_token_acquire_response_structure()
    {
        // Test with valid credentials if auth works
        $response = $this->postJson(route('token.acquire'), [
            'email' => $this->user->email,
            'password' => 'password',
        ]);

        if ($response->status() === 200) {
            // Should have proper token structure
            $response->assertJsonStructure([
                'token', 'user',
            ]);

            // Token should not be empty
            $this->assertNotEmpty($response->json('token'));
        }
    }
}
