<?php

namespace App\Livewire\Profile;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class EnhancedTwoFactorForm extends Component
{
    /**
     * Indicates if two factor authentication QR code is being displayed.
     */
    public bool $showingQrCode = false;

    /**
     * Indicates if two factor authentication recovery codes are being displayed.
     */
    public bool $showingRecoveryCodes = false;

    /**
     * Indicates if two factor authentication is being confirmed.
     */
    public bool $showingConfirmation = false;

    /**
     * The OTP code for confirming two factor authentication.
     */
    public string $code = '';

    /**
     * The current 2FA preference.
     */
    public string $preferred2faMethod = 'totp';

    /**
     * Whether email 2FA is enabled.
     */
    public bool $emailTwoFactorEnabled = false;

    /**
     * The test email code for verification.
     */
    public string $testEmailCode = '';

    /**
     * Whether we're showing the email test form.
     */
    public bool $showingEmailTest = false;

    public function mount()
    {
        $user = Auth::user();
        $this->preferred2faMethod = $user->getPreferred2faMethod();
        $this->emailTwoFactorEnabled = $user->hasEmailTwoFactorEnabled();
    }

    /**
     * Enable two factor authentication for the user.
     */
    public function enableTwoFactorAuthentication()
    {
        $this->ensurePasswordIsConfirmed();

        $user = Auth::user();
        $user->forceFill([
            'two_factor_secret' => encrypt(app('pragmarx.google2fa')->generateSecretKey()),
        ])->save();

        $this->showingQrCode = true;
        $this->showingConfirmation = true;
        $this->showingRecoveryCodes = false;
    }

    /**
     * Confirm two factor authentication for the user.
     */
    public function confirmTwoFactorAuthentication()
    {
        $this->ensurePasswordIsConfirmed();

        $user = Auth::user();

        $confirmed = app(TwoFactorAuthenticationProvider::class)->verify($user, $this->code);

        if ($confirmed) {
            session()->flash('status', 'two-factor-authentication-confirmed');

            $this->showingQrCode = false;
            $this->showingConfirmation = false;
            $this->showingRecoveryCodes = true;
        } else {
            $this->addError('code', __('The provided two factor authentication code was invalid.'));
        }

        $this->code = '';
    }

    /**
     * Regenerate the two factor authentication recovery codes.
     */
    public function regenerateRecoveryCodes()
    {
        $this->ensurePasswordIsConfirmed();

        app(GenerateNewRecoveryCodes::class)(Auth::user());

        $this->showingRecoveryCodes = true;
    }

    /**
     * Display the two factor authentication recovery codes.
     */
    public function showRecoveryCodes()
    {
        $this->ensurePasswordIsConfirmed();

        $this->showingRecoveryCodes = true;
    }

    /**
     * Disable two factor authentication for the user.
     */
    public function disableTwoFactorAuthentication()
    {
        $this->ensurePasswordIsConfirmed();

        app(TwoFactorAuthenticationProvider::class)->disable(Auth::user());

        $this->showingQrCode = false;
        $this->showingConfirmation = false;
        $this->showingRecoveryCodes = false;
    }

    /**
     * Enable email two factor authentication.
     */
    public function enableEmailTwoFactor()
    {
        $this->ensurePasswordIsConfirmed();

        $user = Auth::user();
        $user->enableEmailTwoFactor();

        $this->emailTwoFactorEnabled = true;
        $this->showingEmailTest = true;

        session()->flash('status', 'Email two-factor authentication has been enabled.');
    }

    /**
     * Disable email two factor authentication.
     */
    public function disableEmailTwoFactor()
    {
        $this->ensurePasswordIsConfirmed();

        $user = Auth::user();
        $user->disableEmailTwoFactor();

        $this->emailTwoFactorEnabled = false;
        $this->showingEmailTest = false;

        session()->flash('status', 'Email two-factor authentication has been disabled.');
    }

    /**
     * Send a test email 2FA code.
     */
    public function sendTestEmailCode()
    {
        $this->ensurePasswordIsConfirmed();

        $user = Auth::user();

        if (! $user->hasEmailTwoFactorEnabled()) {
            $this->addError('email', 'Email two-factor authentication is not enabled.');

            return;
        }

        try {
            $user->generateEmailTwoFactorCode();
            session()->flash('status', 'A test verification code has been sent to your email.');
        } catch (\Exception $e) {
            $this->addError('email', $e->getMessage());
        }
    }

    /**
     * Verify the test email code.
     */
    public function verifyTestEmailCode()
    {
        $user = Auth::user();

        $verified = $user->verifyEmailTwoFactorCode($this->testEmailCode);

        if ($verified) {
            session()->flash('status', 'Email verification code confirmed successfully!');
            $this->showingEmailTest = false;
        } else {
            $this->addError('testEmailCode', 'The provided email verification code was invalid or expired.');
        }

        $this->testEmailCode = '';
    }

    /**
     * Update the user's 2FA preference.
     */
    public function updatePreference()
    {
        $this->ensurePasswordIsConfirmed();

        $user = Auth::user();

        try {
            $user->setPreferred2faMethod($this->preferred2faMethod);
            session()->flash('status', 'Two-factor authentication preference updated successfully.');
        } catch (\InvalidArgumentException $e) {
            $this->addError('preferred2faMethod', $e->getMessage());
        }
    }

    /**
     * Get the two factor authentication QR code SVG.
     */
    public function getTwoFactorQrCodeSvgProperty(): ?string
    {
        $user = Auth::user();

        if (! $user->two_factor_secret) {
            return null;
        }

        return app(TwoFactorAuthenticationProvider::class)->qrCodeSvg(
            config('app.name'),
            $user->email,
            decrypt($user->two_factor_secret)
        );
    }

    /**
     * Get the current user's two factor authentication recovery codes.
     */
    public function getRecoveryCodesProperty(): ?array
    {
        $user = Auth::user();

        return $user->recoveryCodes();
    }

    /**
     * Determine if two factor authentication is enabled.
     */
    public function getEnabledProperty(): bool
    {
        return Auth::user()->hasEnabledTwoFactorAuthentication();
    }

    /**
     * Determine if the user has confirmed two factor authentication.
     */
    public function getConfirmedProperty(): bool
    {
        return Auth::user()->hasEnabledTwoFactorAuthentication() &&
               ! is_null(Auth::user()->two_factor_confirmed_at);
    }

    /**
     * Get available 2FA methods for this user.
     */
    public function getAvailable2faMethodsProperty(): array
    {
        return Auth::user()->getAvailable2faMethods();
    }

    /**
     * Ensure that the user has confirmed their password.
     */
    protected function ensurePasswordIsConfirmed(): void
    {
        if (! app(StatefulGuard::class)->confirm()) {
            $this->redirect(route('password.confirm'));
        }
    }

    public function render()
    {
        return view('livewire.profile.enhanced-two-factor-form');
    }
}
