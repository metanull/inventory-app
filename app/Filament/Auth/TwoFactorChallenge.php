<?php

namespace App\Filament\Auth;

use App\Services\Filament\Auth\EmailTwoFactorCodeService;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Pages\SimplePage;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;
use Laravel\Fortify\Fortify;

class TwoFactorChallenge extends SimplePage
{
    use InteractsWithFormActions;

    protected static string $view = 'filament.auth.two-factor-challenge';

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public function mount(): void
    {
        if (! session()->has('filament.admin.2fa.user_id')) {
            $this->redirect(Filament::getLoginUrl());

            return;
        }
    }

    public function form(Form $form): Form
    {
        return $form;
    }

    /**
     * @return array<int|string, string|Form>
     */
    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema([
                        TextInput::make('code')
                            ->label(__('Authentication Code'))
                            ->placeholder('000 000')
                            ->maxLength(8)
                            ->autocomplete('one-time-code')
                            ->autofocus()
                            ->extraInputAttributes(['tabindex' => 1]),
                        TextInput::make('recovery_code')
                            ->label(__('Or use a recovery code'))
                            ->placeholder('xxxxx-xxxxx')
                            ->extraInputAttributes(['tabindex' => 2]),
                        TextInput::make('email_code')
                            ->label(__('Or use an email verification code'))
                            ->placeholder('000000')
                            ->maxLength(6)
                            ->autocomplete('one-time-code')
                            ->hint(__('Request a code to be sent to your verified email address.'))
                            ->extraInputAttributes(['tabindex' => 3]),
                    ])
                    ->statePath('data'),
            ),
        ];
    }

    public function sendEmailCode(): void
    {
        $loginId = session('filament.admin.2fa.user_id');

        if (! $loginId) {
            $this->redirect(Filament::getLoginUrl());

            return;
        }

        $userModel = config('auth.providers.users.model');
        $user = $userModel::find($loginId);

        if (! $user) {
            session()->forget(['filament.admin.2fa.user_id', 'filament.admin.2fa.remember', 'filament.admin.2fa.email_challenge_id']);
            $this->redirect(Filament::getLoginUrl());

            return;
        }

        try {
            app(EmailTwoFactorCodeService::class)->send($user);

            Notification::make()
                ->success()
                ->title(__('Verification code sent to your email address.'))
                ->send();
        } catch (\Throwable) {
            throw ValidationException::withMessages([
                'data.email_code' => [__('Unable to send verification code. Please try a different verification method.')],
            ]);
        }
    }

    public function submit(): void
    {
        $loginId = session('filament.admin.2fa.user_id');
        $limiterKey = 'two-factor:'.$loginId;
        $maxAttempts = app()->environment('testing') ? 50 : 5;

        if (RateLimiter::tooManyAttempts($limiterKey, $maxAttempts)) {
            $availableIn = RateLimiter::availableIn($limiterKey);

            session()->forget(['filament.admin.2fa.user_id', 'filament.admin.2fa.remember', 'filament.admin.2fa.email_challenge_id']);

            Notification::make()
                ->danger()
                ->title(__('filament-panels::pages/auth/login.notifications.throttled.title', [
                    'seconds' => $availableIn,
                    'minutes' => (int) ceil($availableIn / 60),
                ]))
                ->send();

            $this->redirect(Filament::getLoginUrl());

            return;
        }

        $data = $this->form->getState();

        $code = trim((string) ($data['code'] ?? ''));
        $recoveryCode = trim((string) ($data['recovery_code'] ?? ''));
        $emailCode = trim((string) ($data['email_code'] ?? ''));

        $credentialCount = (int) ($code !== '') + (int) ($recoveryCode !== '') + (int) ($emailCode !== '');

        if ($credentialCount !== 1) {
            throw ValidationException::withMessages([
                'data.code' => [__('Please provide exactly one verification method.')],
            ]);
        }

        $userModel = config('auth.providers.users.model');
        $user = $userModel::find($loginId);

        if (! $user) {
            session()->forget(['filament.admin.2fa.user_id', 'filament.admin.2fa.remember', 'filament.admin.2fa.email_challenge_id']);
            $this->redirect(Filament::getLoginUrl());

            return;
        }

        $verified = false;

        if ($recoveryCode !== '') {
            $recoveryCodes = collect($user->recoveryCodes());
            $matched = $recoveryCodes->first(fn ($rc) => hash_equals($rc, $recoveryCode));

            if ($matched) {
                $user->replaceRecoveryCode($matched);
                $verified = true;
            }
        } elseif ($code !== '') {
            $provider = app(TwoFactorAuthenticationProvider::class);
            $secret = Fortify::currentEncrypter()->decrypt($user->two_factor_secret);
            $verified = $provider->verify($secret, $code);
        } elseif ($emailCode !== '') {
            $verified = app(EmailTwoFactorCodeService::class)->verify($user, $emailCode);
        }

        if (! $verified) {
            RateLimiter::hit($limiterKey);

            if ($emailCode !== '') {
                throw ValidationException::withMessages([
                    'data.email_code' => [__('The provided email verification code was invalid or has expired.')],
                ]);
            }

            throw ValidationException::withMessages([
                'data.code' => [__('The provided two factor authentication code was invalid.')],
            ]);
        }

        RateLimiter::clear($limiterKey);

        $guard = Auth::guard(Filament::getAuthGuard());
        $guard->login($user, session()->pull('filament.admin.2fa.remember', false));

        session()->regenerate();
        session()->forget(['filament.admin.2fa.user_id', 'filament.admin.2fa.email_challenge_id']);

        $this->redirect(Filament::getUrl());
    }

    /**
     * @return array<Action>
     */
    protected function getFormActions(): array
    {
        return [
            Action::make('submit')
                ->label(__('Verify'))
                ->submit('submit'),
            Action::make('sendEmailCode')
                ->label(__('Send code to my email'))
                ->action('sendEmailCode')
                ->color('gray')
                ->outlined(),
        ];
    }

    protected function hasFullWidthFormActions(): bool
    {
        return true;
    }

    public function getTitle(): string|Htmlable
    {
        return __('Two-Factor Authentication');
    }

    public function getHeading(): string|Htmlable
    {
        return __('Two-Factor Authentication');
    }

    public static function getSlug(): string
    {
        return 'two-factor-challenge';
    }

    public static function getRelativeRouteName(): string
    {
        return 'auth.two-factor-challenge';
    }
}
