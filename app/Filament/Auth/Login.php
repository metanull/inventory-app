<?php

namespace App\Filament\Auth;

use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Facades\Filament;
use Filament\Http\Responses\Auth\Contracts\LoginResponse as FilamentLoginResponse;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Session\Session;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Actions\AttemptToAuthenticate;
use Laravel\Fortify\Actions\CanonicalizeUsername;
use Laravel\Fortify\Actions\EnsureLoginIsNotThrottled;
use Laravel\Fortify\Actions\PrepareAuthenticatedSession;
use Laravel\Fortify\Contracts\RedirectsIfTwoFactorAuthenticatable;
use Laravel\Fortify\Features;
use Laravel\Fortify\Fortify;

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

        $session = request()->hasSession()
            ? request()->session()
            : app('session.store');

        $session->put('filament.auth.panel', Filament::getCurrentPanel()->getId());

        $request = $this->makeFortifyRequest($data, $session);

        try {
            $response = $this->loginPipeline($request)->then(function (Request $request) {
                $user = Filament::auth()->user();

                if (($user instanceof FilamentUser) && (! $user->canAccessPanel(Filament::getCurrentPanel()))) {
                    Filament::auth()->logout();
                    $request->session()->forget('filament.auth.panel');

                    $this->throwFailureValidationException();
                }
            });
        } catch (ValidationException $exception) {
            $request->session()->forget('filament.auth.panel');

            throw ValidationException::withMessages([
                'data.email' => $exception->errors()[Fortify::username()] ?? [__('filament-panels::pages/auth/login.messages.failed')],
            ]);
        }

        $this->ensurePendingTwoFactorUserCanAccessPanel($request);

        if ($response instanceof RedirectResponse) {
            $this->redirect($response->getTargetUrl());

            return null;
        }

        $user = Filament::auth()->user();

        if (! $user) {
            // A non-HttpFoundation redirect was already queued by the pipeline
            // (e.g. Livewire's Redirector wrapping a two-factor challenge redirect).
            // Return null to let the queued redirect take effect.
            return null;
        }

        if (($user instanceof MustVerifyEmail) && (! $user->hasVerifiedEmail())) {
            $request->session()->forget('filament.auth.panel');
            $this->redirectRoute('verification.notice');

            return null;
        }

        if (is_null($user->two_factor_confirmed_at)) {
            $this->redirect(Filament::getCurrentPanel()->route('auth.two-factor-setup'));

            return null;
        }

        return app(FilamentLoginResponse::class);
    }

    protected function loginPipeline(Request $request): Pipeline
    {
        if (Fortify::$authenticateThroughCallback) {
            return (new Pipeline(app()))->send($request)->through(array_filter(
                call_user_func(Fortify::$authenticateThroughCallback, $request)
            ));
        }

        if (is_array(config('fortify.pipelines.login'))) {
            return (new Pipeline(app()))->send($request)->through(array_filter(
                config('fortify.pipelines.login')
            ));
        }

        return (new Pipeline(app()))->send($request)->through(array_filter([
            config('fortify.limiters.login') ? null : EnsureLoginIsNotThrottled::class,
            config('fortify.lowercase_usernames') ? CanonicalizeUsername::class : null,
            Features::enabled(Features::twoFactorAuthentication()) ? RedirectsIfTwoFactorAuthenticatable::class : null,
            AttemptToAuthenticate::class,
            PrepareAuthenticatedSession::class,
        ]));
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function makeFortifyRequest(array $data, Session $session): Request
    {
        $request = request()->duplicate(
            null,
            [
                Fortify::username() => $data['email'],
                'password' => $data['password'],
                'remember' => (bool) ($data['remember'] ?? false),
            ],
        );

        $request->headers->set('Accept', 'text/html');
        $request->setLaravelSession($session);

        return $request;
    }

    protected function ensurePendingTwoFactorUserCanAccessPanel(Request $request): void
    {
        $loginId = $request->session()->get('login.id');

        if (! $loginId) {
            return;
        }

        $user = config('auth.providers.users.model')::find($loginId);

        if (($user instanceof FilamentUser) && $user->canAccessPanel(Filament::getCurrentPanel())) {
            return;
        }

        $request->session()->forget([
            'login.id',
            'login.remember',
            'filament.auth.panel',
        ]);

        $this->throwFailureValidationException();
    }
}
