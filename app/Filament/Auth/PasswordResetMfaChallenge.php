<?php

namespace App\Filament\Auth;

use App\Models\User;
use App\Services\Filament\Auth\EmailTwoFactorCodeService;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\Actions\Action as FormAction;
use Filament\Forms\Components\Actions as FormActions;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Pages\SimplePage;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;
use Laravel\Fortify\Fortify;

class PasswordResetMfaChallenge extends SimplePage
{
    use InteractsWithFormActions;

    protected static string $view = 'filament.auth.password-reset-mfa-challenge';

    private const MAX_ATTEMPTS_PRODUCTION = 5;

    private const MAX_ATTEMPTS_TESTING = 50;

    private const USER_ID_SESSION_KEY = 'filament.admin.password_reset.user_id';

    private const PASSWORD_HASH_SESSION_KEY = 'filament.admin.password_reset.password_hash';

    private const TOKEN_SESSION_KEY = 'filament.admin.password_reset.token';

    private const CHALLENGE_ID_SESSION_KEY = 'filament.admin.password_reset.email_challenge_id';

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public function mount(): void
    {
        if (! session()->has(self::USER_ID_SESSION_KEY)) {
            $this->redirect(Filament::getLoginUrl());

            return;
        }

        $this->getForm('form')?->fill();
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
                        Radio::make('method')
                            ->label(__('Verification method'))
                            ->options([
                                'totp' => __('Authenticator app'),
                                'recovery' => __('Recovery code'),
                                'email' => __('Email code'),
                            ])
                            ->default('totp')
                            ->inline()
                            ->live(),
                        TextInput::make('code')
                            ->label(__('Authentication Code'))
                            ->placeholder('000 000')
                            ->maxLength(8)
                            ->autocomplete('one-time-code')
                            ->autofocus()
                            ->hidden(fn (Get $get): bool => $get('method') !== 'totp')
                            ->extraInputAttributes(['tabindex' => 1]),
                        TextInput::make('recovery_code')
                            ->label(__('Recovery Code'))
                            ->placeholder('xxxxx-xxxxx')
                            ->hidden(fn (Get $get): bool => $get('method') !== 'recovery')
                            ->extraInputAttributes(['tabindex' => 2]),
                        TextInput::make('email_code')
                            ->label(__('Email Verification Code'))
                            ->placeholder('000000')
                            ->maxLength(6)
                            ->autocomplete('one-time-code')
                            ->hint(__('Enter the code sent to your email address.'))
                            ->hidden(fn (Get $get): bool => $get('method') !== 'email')
                            ->extraInputAttributes(['tabindex' => 3]),
                        FormActions::make([
                            FormAction::make('sendEmailCode')
                                ->label(__('Send code to my email'))
                                ->color('gray')
                                ->outlined()
                                ->action(fn ($livewire) => $livewire->sendEmailCode()),
                        ])
                            ->hidden(fn (Get $get): bool => $get('method') !== 'email')
                            ->columnSpanFull(),
                    ])
                    ->statePath('data'),
            ),
        ];
    }

    public function sendEmailCode(): void
    {
        $userId = session(self::USER_ID_SESSION_KEY);

        if (! $userId) {
            $this->redirect(Filament::getLoginUrl());

            return;
        }

        $user = User::find($userId);

        if (! $user) {
            $this->clearPasswordResetSession();
            $this->redirect(Filament::getLoginUrl());

            return;
        }

        try {
            app(EmailTwoFactorCodeService::class)->send(
                $user,
                self::USER_ID_SESSION_KEY,
                self::CHALLENGE_ID_SESSION_KEY,
            );

            Notification::make()
                ->success()
                ->title(__('Verification code sent to your email address.'))
                ->send();
        } catch (\RuntimeException) {
            throw ValidationException::withMessages([
                'data.email_code' => [__('Unable to send verification code. Please try a different verification method.')],
            ]);
        }
    }

    public function submit(): void
    {
        $userId = session(self::USER_ID_SESSION_KEY);
        $limiterKey = 'password-reset-mfa:'.$userId;
        $maxAttempts = app()->environment('testing') ? self::MAX_ATTEMPTS_TESTING : self::MAX_ATTEMPTS_PRODUCTION;

        if (RateLimiter::tooManyAttempts($limiterKey, $maxAttempts)) {
            $availableIn = RateLimiter::availableIn($limiterKey);

            $this->clearPasswordResetSession();

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

        $data = $this->getForm('form')?->getState() ?? [];

        $method = $data['method'] ?? 'totp';

        $code = $method === 'totp' ? trim((string) ($data['code'] ?? '')) : '';
        $recoveryCode = $method === 'recovery' ? trim((string) ($data['recovery_code'] ?? '')) : '';
        $emailCode = $method === 'email' ? trim((string) ($data['email_code'] ?? '')) : '';

        if ($code === '' && $recoveryCode === '' && $emailCode === '') {
            throw ValidationException::withMessages([
                'data.'.$this->fieldForMethod($method) => [__('Please enter your verification code.')],
            ]);
        }

        $user = User::find($userId);

        if (! $user) {
            $this->clearPasswordResetSession();
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
            $verified = app(EmailTwoFactorCodeService::class)->verify(
                $user,
                $emailCode,
                self::USER_ID_SESSION_KEY,
                self::CHALLENGE_ID_SESSION_KEY,
            );
        }

        if (! $verified) {
            RateLimiter::hit($limiterKey);

            throw ValidationException::withMessages([
                'data.'.$this->fieldForMethod($method) => [$this->errorForMethod($method)],
            ]);
        }

        RateLimiter::clear($limiterKey);

        // Pull all pending reset state; fail fast if any piece is missing
        $pendingHash = session()->pull(self::PASSWORD_HASH_SESSION_KEY);
        $pendingToken = session()->pull(self::TOKEN_SESSION_KEY);
        $this->clearPasswordResetSession();

        if (! $pendingHash || ! $pendingToken) {
            $this->redirect(Filament::getLoginUrl());

            return;
        }

        // Re-check token validity after MFA succeeds, before applying the password change
        $tokenRepository = Password::broker(config('fortify.passwords'))->getRepository();

        if (! $tokenRepository->exists($user, $pendingToken)) {
            Notification::make()
                ->warning()
                ->title(__('The password reset token has expired. Please request a new reset link.'))
                ->send();

            $this->redirect(Filament::getLoginUrl());

            return;
        }

        $user->forceFill(['password' => $pendingHash])->save();
        Password::broker(config('fortify.passwords'))->deleteToken($user);

        Notification::make()
            ->title(__('Your password has been reset. You may now log in.'))
            ->success()
            ->send();

        Filament::auth()->logout();
        $this->redirect(Filament::getLoginUrl());
    }

    private function fieldForMethod(string $method): string
    {
        return match ($method) {
            'recovery' => 'recovery_code',
            'email' => 'email_code',
            default => 'code',
        };
    }

    private function errorForMethod(string $method): string
    {
        return match ($method) {
            'email' => __('The provided email verification code was invalid or has expired.'),
            default => __('The provided two factor authentication code was invalid.'),
        };
    }

    private function clearPasswordResetSession(): void
    {
        session()->forget([
            self::USER_ID_SESSION_KEY,
            self::PASSWORD_HASH_SESSION_KEY,
            self::TOKEN_SESSION_KEY,
            self::CHALLENGE_ID_SESSION_KEY,
        ]);
    }

    /**
     * @return array<Action>
     */
    protected function getFormActions(): array
    {
        return [
            Action::make('submit')
                ->label(__('Verify and reset password'))
                ->submit('submit'),
        ];
    }

    protected function hasFullWidthFormActions(): bool
    {
        return true;
    }

    public function getTitle(): string|Htmlable
    {
        return __('Confirm Identity');
    }

    public function getHeading(): string|Htmlable
    {
        return __('Two-Factor Authentication Required');
    }

    public static function getSlug(): string
    {
        return 'reset-password-mfa';
    }

    public static function getRelativeRouteName(): string
    {
        return 'auth.password.reset.mfa';
    }
}
