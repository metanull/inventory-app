<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;

class MobileAppAuthenticationController extends Controller
{
    /**
     * Acquire a token for the user.
     *
     * @unauthenticated
     */
    public function acquire_token(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'device_name' => 'required|string|max:255',
            'wipe_tokens' => 'sometimes|boolean',
        ]);

        $user = User::where('email', $request->email)->first();
        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Check if user has 2FA enabled
        if ($user->hasTwoFactorEnabled()) {
            return $this->requireTwoFactorAuthentication($user);
        }

        // No 2FA required, issue token directly
        if ($request->boolean('wipe_tokens', false)) {
            $user->tokens()->delete();
        }

        return response()->json([
            'token' => $user->createToken($request->device_name)->plainTextToken,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'two_factor_enabled' => false,
            ],
        ], 201);
    }

    /**
     * Verify two-factor authentication and acquire token.
     *
     * @unauthenticated
     */
    public function verify_two_factor(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'device_name' => 'required|string|max:255',
            'code' => 'required|string',
            'method' => 'sometimes|string|in:totp,email',
            'wipe_tokens' => 'sometimes|boolean',
        ]);

        $user = User::where('email', $request->email)->first();
        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (! $user->hasTwoFactorEnabled()) {
            throw ValidationException::withMessages([
                'code' => ['Two-factor authentication is not enabled for this account.'],
            ]);
        }

        $method = $request->input('method', $user->getPrimary2faMethod());
        $verified = false;

        // Verify based on method
        switch ($method) {
            case 'totp':
                if ($user->canUseTotpFor2fa()) {
                    $verified = app(TwoFactorAuthenticationProvider::class)->verify($user, $request->code);
                }
                break;

            case 'email':
                if ($user->canUseEmailFor2fa()) {
                    $verified = $user->verifyEmailTwoFactorCode($request->code);
                }
                break;

            default:
                // Try both methods if no specific method requested
                if ($user->canUseTotpFor2fa()) {
                    $verified = app(TwoFactorAuthenticationProvider::class)->verify($user, $request->code);
                }

                if (! $verified && $user->canUseEmailFor2fa()) {
                    $verified = $user->verifyEmailTwoFactorCode($request->code);
                }
                break;
        }

        if (! $verified) {
            throw ValidationException::withMessages([
                'code' => ['The provided two-factor authentication code was invalid.'],
            ]);
        }

        // 2FA verified, issue token
        if ($request->boolean('wipe_tokens', false)) {
            $user->tokens()->delete();
        }

        return response()->json([
            'token' => $user->createToken($request->device_name)->plainTextToken,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'two_factor_enabled' => true,
                'two_factor_method' => $method,
            ],
        ], 201);
    }

    /**
     * Request an email 2FA code for mobile authentication.
     *
     * @unauthenticated
     */
    public function request_email_code(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();
        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (! $user->canUseEmailFor2fa()) {
            throw ValidationException::withMessages([
                'email' => ['Email two-factor authentication is not available for this account.'],
            ]);
        }

        try {
            $user->generateEmailTwoFactorCode();

            return response()->json([
                'message' => 'Email verification code sent successfully.',
                'expires_in' => config('auth.email_2fa.expiry_minutes', 5) * 60, // in seconds
            ]);
        } catch (\Exception $e) {
            throw ValidationException::withMessages([
                'email' => [$e->getMessage()],
            ]);
        }
    }

    /**
     * Get user's 2FA status and available methods.
     *
     * @unauthenticated
     */
    public function two_factor_status(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();
        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        return response()->json([
            'two_factor_enabled' => $user->hasTwoFactorEnabled(),
            'available_methods' => $user->getAvailable2faMethods(),
            'primary_method' => $user->getPrimary2faMethod(),
            'requires_two_factor' => $user->needs2faVerification(),
        ]);
    }

    /**
     * Return a 2FA challenge response.
     */
    protected function requireTwoFactorAuthentication(User $user)
    {
        $availableMethods = $user->getAvailable2faMethods();
        $primaryMethod = $user->getPrimary2faMethod();

        return response()->json([
            'requires_two_factor' => true,
            'available_methods' => $availableMethods,
            'primary_method' => $primaryMethod,
            'message' => 'Two-factor authentication required. Please provide a verification code.',
        ], 202); // 202 Accepted - request received but requires additional action
    }

    /**
     * Revoke all the token for the current user.
     */
    public function wipe_tokens(Request $request)
    {
        if (! $request->user()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }
        $request->user()->tokens()->delete();

        return response()->noContent();
    }
}
