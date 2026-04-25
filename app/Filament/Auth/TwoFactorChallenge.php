<?php

namespace App\Filament\Auth;

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
        if (! session()->has('login.id')) {
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
                    ])
                    ->statePath('data'),
            ),
        ];
    }

    public function submit(): void
    {
        $loginId = session('login.id');
        $limiterKey = 'two-factor:'.$loginId;
        $maxAttempts = app()->environment('testing') ? 50 : 5;

        if (RateLimiter::tooManyAttempts($limiterKey, $maxAttempts)) {
            $availableIn = RateLimiter::availableIn($limiterKey);

            session()->forget(['login.id', 'login.remember', 'filament.auth.panel']);

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

        $userModel = config('auth.providers.users.model');
        $user = $userModel::find($loginId);

        if (! $user) {
            session()->forget(['login.id', 'login.remember', 'filament.auth.panel']);
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
        }

        if (! $verified) {
            RateLimiter::hit($limiterKey);

            throw ValidationException::withMessages([
                'data.code' => [__('The provided two factor authentication code was invalid.')],
            ]);
        }

        RateLimiter::clear($limiterKey);

        $guard = Auth::guard(Filament::getAuthGuard());
        $guard->login($user, session()->pull('login.remember', false));

        session()->regenerate();
        session()->forget(['login.id', 'filament.auth.panel']);

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
