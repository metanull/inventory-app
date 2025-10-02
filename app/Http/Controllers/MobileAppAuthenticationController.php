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
            'two_factor_code' => 'sometimes|string',
            'recovery_code' => 'sometimes|string',
        ]);

        $user = User::where('email', $request->email)->first();
        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Check if email verification is required
        if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail()) {
            throw ValidationException::withMessages([
                'email' => ['Your email address is not verified.'],
            ]);
        }

        // Check if user has 2FA enabled
        if ($user->hasTwoFactorEnabled()) {
            // If 2FA code or recovery code provided, verify it
            if ($request->filled('two_factor_code') || $request->filled('recovery_code')) {
                $code = $request->two_factor_code ?? $request->recovery_code;
                $isRecovery = $request->filled('recovery_code');

                if ($this->verifyTwoFactorCode($user, $code, $isRecovery)) {
                    // 2FA verification successful, proceed to issue token
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
                        ],
                    ], 201);
                } else {
                    // 2FA verification failed
                    $fieldName = $isRecovery ? 'recovery_code' : 'two_factor_code';
                    throw ValidationException::withMessages([
                        $fieldName => ['The provided two-factor authentication code is invalid.'],
                    ]);
                }
            } else {
                // No 2FA code provided, require 2FA
                return $this->requireTwoFactorAuthentication($user);
            }
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
                    try {
                        $decryptedSecret = decrypt($user->two_factor_secret);
                        $verified = app(TwoFactorAuthenticationProvider::class)->verify($decryptedSecret, $request->code);
                    } catch (\PragmaRX\Google2FA\Exceptions\InvalidCharactersException $e) {
                        // Invalid TOTP secret in database - treat as invalid code
                        $verified = false;
                    } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
                        // Invalid encrypted secret in database - treat as invalid code
                        $verified = false;
                    }
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
                    try {
                        $decryptedSecret = decrypt($user->two_factor_secret);
                        $verified = app(TwoFactorAuthenticationProvider::class)->verify($decryptedSecret, $request->code);
                    } catch (\PragmaRX\Google2FA\Exceptions\InvalidCharactersException $e) {
                        // Invalid TOTP secret in database - treat as invalid code
                        $verified = false;
                    } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
                        // Invalid encrypted secret in database - treat as invalid code
                        $verified = false;
                    }
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

    /**
     * Verify two-factor authentication code for a user.
     */
    private function verifyTwoFactorCode(User $user, string $code, bool $isRecovery = false): bool
    {
        if ($isRecovery) {
            return $user->validateAndConsumeRecoveryCode($code);
        }

        // Try TOTP first if enabled
        if ($user->canUseTotpFor2fa()) {
            try {
                $decryptedSecret = decrypt($user->two_factor_secret);
                if (app(TwoFactorAuthenticationProvider::class)->verify($decryptedSecret, $code)) {
                    return true;
                }
            } catch (\PragmaRX\Google2FA\Exceptions\InvalidCharactersException $e) {
                // Invalid TOTP secret in database - treat as invalid
            } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
                // Invalid encrypted secret in database - treat as invalid
            }
        }

        // Try email 2FA if TOTP failed and email 2FA is enabled
        if ($user->canUseEmailFor2fa()) {
            return $user->verifyEmailTwoFactorCode($code);
        }

        return false;
    }
}
