<?php

namespace App\Filament\Auth;

use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Facades\Filament;
use Filament\Http\Responses\Auth\Contracts\LoginResponse as FilamentLoginResponse;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laravel\Fortify\Features;

class Login extends \Filament\Pages\Auth\Login
{
    public function authenticate(): ?FilamentLoginResponse
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();

            return null;
        }

        $data = $this->form->getState();

        $guard = Filament::auth();
        $provider = $guard->getProvider();

        $user = $provider->retrieveByCredentials(['email' => $data['email']]);

        if (! $user || ! $provider->validateCredentials($user, ['password' => $data['password']])) {
            $this->throwFailureValidationException();
        }

        if (! ($user instanceof FilamentUser) || ! $user->canAccessPanel(Filament::getCurrentPanel())) {
            $this->throwFailureValidationException();
        }

        $remember = (bool) ($data['remember'] ?? false);

        if (
            Features::enabled(Features::twoFactorAuthentication()) &&
            ! empty($user->two_factor_secret) &&
            ! is_null($user->two_factor_confirmed_at)
        ) {
            session()->put('filament.admin.2fa.user_id', $user->getKey());
            session()->put('filament.admin.2fa.remember', $remember);

            $this->redirect(Filament::getCurrentPanel()->route('auth.two-factor-challenge'));

            return null;
        }

        $guard->login($user, $remember);

        if (($user instanceof MustVerifyEmail) && ! $user->hasVerifiedEmail()) {
            $this->redirectRoute('verification.notice');

            return null;
        }

        if (Features::enabled(Features::twoFactorAuthentication()) && is_null($user->two_factor_confirmed_at)) {
            $this->redirect(Filament::getCurrentPanel()->route('auth.two-factor-setup'));

            return null;
        }

        return app(FilamentLoginResponse::class);
    }
}
