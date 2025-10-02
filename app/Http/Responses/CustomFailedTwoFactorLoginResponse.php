<?php

namespace App\Http\Responses;

use App\Services\EmailTwoFactorService;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\FailedTwoFactorLoginResponse as FailedTwoFactorLoginResponseContract;

class CustomFailedTwoFactorLoginResponse implements FailedTwoFactorLoginResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        // Get the user from session
        $userId = $request->session()->get('login.id');
        if (! $userId) {
            return redirect()->route('login');
        }

        $user = app(config('auth.providers.users.model'))->find($userId);
        if (! $user) {
            return redirect()->route('login');
        }

        // Check if we should validate email 2FA for this user
        if ($user->hasEmailTwoFactorEnabled() && $request->filled('code')) {
            $emailTwoFactorService = app(EmailTwoFactorService::class);

            Log::info('CustomFailedTwoFactorLoginResponse: Attempting email 2FA validation', [
                'user_id' => $user->id,
                'code' => $request->code,
            ]);

            // If email 2FA validation succeeds, continue with normal login flow
            if ($emailTwoFactorService->verifyCode($user, $request->code)) {
                Log::info('CustomFailedTwoFactorLoginResponse: Email 2FA validation succeeded, logging user in');

                // Log the user in
                app('auth')->guard()->login($user, $request->boolean('remember'));
                $request->session()->regenerate();
                $request->session()->forget('login.id');

                return redirect()->intended(config('fortify.home'));
            }

            Log::info('CustomFailedTwoFactorLoginResponse: Email 2FA validation failed');
            // If email 2FA validation failed, we should fall through to return the error
        }

        // If validation failed, return error
        [$key, $message] = $request->filled('recovery_code')
            ? ['recovery_code', __('The provided two factor recovery code was invalid.')]
            : ['code', __('The provided two factor authentication code was invalid.')];

        if ($request->wantsJson()) {
            throw ValidationException::withMessages([
                $key => [$message],
            ]);
        }

        return redirect()->route('two-factor.login')->withErrors([$key => $message]);
    }
}
