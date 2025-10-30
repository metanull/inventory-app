<?php

namespace Tests\Web\Authentication;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Fortify\Features;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class TotpTwoFactorTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        // 2FA setup/verification only requires authentication, no specific permissions
        $this->user = User::factory()->create(['email_verified_at' => now()]);
        $this->actingAs($this->user);
    }

    public function test_totp_two_factor_authentication_setup_generates_qr_code(): void
    {
        if (! Features::canManageTwoFactorAuthentication()) {
            $this->markTestSkipped('Two factor authentication is not enabled.');
        }

        $this->withSession(['auth.password_confirmed_at' => time()]);

        // Enable 2FA
        $response = $this->post('/web/user/two-factor-authentication');

        $updatedUser = $this->user->fresh();
        $this->assertNotNull($updatedUser->two_factor_secret);

        // Test QR code generation
        $qrResponse = $this->get('/web/user/two-factor-qr-code');
        $qrResponse->assertOk();
        $this->assertStringContainsString('application/json', $qrResponse->headers->get('content-type'));

        $qrData = json_decode($qrResponse->getContent(), true);
        $this->assertArrayHasKey('svg', $qrData);
        $this->assertStringContainsString('<svg', $qrData['svg']);
    }

    public function test_totp_two_factor_authentication_generates_recovery_codes(): void
    {
        if (! Features::canManageTwoFactorAuthentication()) {
            $this->markTestSkipped('Two factor authentication is not enabled.');
        }

        $this->withSession(['auth.password_confirmed_at' => time()]);

        // Enable 2FA
        $response = $this->post('/web/user/two-factor-authentication');

        $updatedUser = $this->user->fresh();
        $this->assertCount(8, $updatedUser->recoveryCodes());

        // Get recovery codes
        $codesResponse = $this->get('/web/user/two-factor-recovery-codes');
        $codesResponse->assertOk();

        $codes = json_decode($codesResponse->getContent(), true);
        $this->assertCount(8, $codes);
    }

    public function test_totp_two_factor_authentication_can_verify_valid_codes(): void
    {
        if (! Features::canManageTwoFactorAuthentication()) {
            $this->markTestSkipped('Two factor authentication is not enabled.');
        }

        $this->withSession(['auth.password_confirmed_at' => time()]);

        // Enable 2FA
        $this->post('/web/user/two-factor-authentication');

        $updatedUser = $this->user->fresh();

        // Generate a valid TOTP code
        $google2fa = new Google2FA;
        $secret = decrypt($updatedUser->two_factor_secret);
        $validCode = $google2fa->getCurrentOtp($secret);

        // Confirm 2FA with valid code
        $response = $this->post('/web/user/confirmed-two-factor-authentication', [
            'code' => $validCode,
        ]);

        $response->assertRedirect();

        $confirmedUser = $this->user->fresh();
        $this->assertNotNull($confirmedUser->two_factor_confirmed_at);
    }

    public function test_totp_two_factor_authentication_requires_password_confirmation(): void
    {
        if (! Features::canManageTwoFactorAuthentication()) {
            $this->markTestSkipped('Two factor authentication is not enabled.');
        }

        // Try to enable 2FA without password confirmation
        $response = $this->post('/web/user/two-factor-authentication');

        $response->assertRedirect('/web/user/confirm-password');
    }

    public function test_two_factor_secret_key_can_be_retrieved(): void
    {
        if (! Features::canManageTwoFactorAuthentication()) {
            $this->markTestSkipped('Two factor authentication is not enabled.');
        }

        $this->withSession(['auth.password_confirmed_at' => time()]);

        // Enable 2FA
        $this->post('/web/user/two-factor-authentication');

        $updatedUser = $this->user->fresh();

        // Get secret key
        $secretResponse = $this->get('/web/user/two-factor-secret-key');
        $secretResponse->assertOk();

        $secretData = json_decode($secretResponse->getContent(), true);
        $this->assertIsString($secretData['secretKey']);
        $this->assertGreaterThan(0, strlen($secretData['secretKey']));
    }
}
