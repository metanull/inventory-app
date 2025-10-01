<?php

namespace App\Livewire\Profile;

use App\Services\EmailTwoFactorService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class EmailTwoFactorForm extends Component
{
    /**
     * Whether email 2FA is enabled.
     */
    public bool $emailTwoFactorEnabled = false;

    /**
     * The current 2FA preference.
     */
    public string $preferred2faMethod = 'totp';

    /**
     * The test email code for verification.
     */
    public string $testEmailCode = '';

    /**
     * Whether we're showing the email test form.
     */
    public bool $showingEmailTest = false;

    /**
     * The EmailTwoFactorService instance.
     */
    protected EmailTwoFactorService $emailService;

    public function boot()
    {
        $this->emailService = app(EmailTwoFactorService::class);
    }

    public function mount()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $this->emailTwoFactorEnabled = $user->hasEmailTwoFactorEnabled();
        $this->preferred2faMethod = $user->getPreferred2faMethod();
    }

    /**
     * Enable email two factor authentication.
     */
    public function enableEmailTwoFactor()
    {
        $this->ensurePasswordIsConfirmed();

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $this->emailService->enableEmailTwoFactor($user);

        $this->emailTwoFactorEnabled = true;
        $this->showingEmailTest = true;

        session()->flash('status', 'Email two-factor authentication has been enabled.');
        $this->dispatch('email-2fa-enabled');
    }

    /**
     * Disable email two factor authentication.
     */
    public function disableEmailTwoFactor()
    {
        $this->ensurePasswordIsConfirmed();

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $this->emailService->disableEmailTwoFactor($user);

        $this->emailTwoFactorEnabled = false;
        $this->showingEmailTest = false;

        session()->flash('status', 'Email two-factor authentication has been disabled.');
        $this->dispatch('email-2fa-disabled');
    }

    /**
     * Send a test email 2FA code.
     */
    public function sendTestEmailCode()
    {
        $this->ensurePasswordIsConfirmed();

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (! $this->emailService->isEmailTwoFactorEnabled($user)) {
            $this->addError('email', 'Email two-factor authentication is not enabled.');

            return;
        }

        try {
            $this->emailService->generateAndSendCode($user);
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
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $verified = $this->emailService->verifyCode($user, $this->testEmailCode);

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

        /** @var \App\Models\User $user */
        $user = Auth::user();

        try {
            $this->emailService->setTwoFactorPreference($user, $this->preferred2faMethod);
            session()->flash('status', 'Two-factor authentication preference updated successfully.');
            $this->dispatch('2fa-preference-updated');
        } catch (\InvalidArgumentException $e) {
            $this->addError('preferred2faMethod', $e->getMessage());
        }
    }

    /**
     * Get available preference options based on what the user has enabled.
     */
    public function getAvailablePreferencesProperty(): array
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $preferences = [];

        // Always show TOTP if it's enabled
        if ($user->hasTotpEnabled()) {
            $preferences['totp'] = 'Authenticator App (TOTP)';
        }

        // Show email if it's enabled
        if ($this->emailTwoFactorEnabled) {
            $preferences['email'] = 'Email Code';
        }

        // Show both option only if both are enabled
        if ($user->hasTotpEnabled() && $this->emailTwoFactorEnabled) {
            $preferences['both'] = 'Both Methods Available';
        }

        return $preferences;
    }

    /**
     * Ensure that the user has confirmed their password.
     */
    protected function ensurePasswordIsConfirmed(): void
    {
        $route = request()->route();
        $currentRoute = $route ? $route->getName() : null;

        if ($currentRoute !== 'password.confirm' && config('auth.password_timeout') > 0) {
            $this->redirect(route('password.confirm'));
        }
    }

    public function render()
    {
        return view('livewire.profile.email-two-factor-form');
    }
}
