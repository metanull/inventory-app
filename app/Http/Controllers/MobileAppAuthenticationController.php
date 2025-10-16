<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\AcquireTokenMobileAppAuthenticationRequest;
use App\Http\Requests\Api\RequestEmailCodeMobileAppAuthenticationRequest;
use App\Http\Requests\Api\TwoFactorStatusMobileAppAuthenticationRequest;
use App\Http\Requests\Api\VerifyTwoFactorMobileAppAuthenticationRequest;
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
    public function acquire_token(AcquireTokenMobileAppAuthenticationRequest $request)
    {
        $validated = $request->validated();

        $user = User::where('email', $validated['email'])->first();
        if (! $user || ! Hash::check($validated['password'], $user->password)) {
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
            if (! empty($validated['two_factor_code']) || ! empty($validated['recovery_code'])) {
                $code = $validated['two_factor_code'] ?? $validated['recovery_code'];
                $isRecovery = ! empty($validated['recovery_code']);

                if ($this->verifyTwoFactorCode($user, $code, $isRecovery)) {
                    // 2FA verification successful, proceed to issue token
                    if ($validated['wipe_tokens'] ?? false) {
                        $user->tokens()->delete();
                    }

                    return response()->json(
                        (new \App\Http\Resources\AuthTokenResource([
                            'token' => $user->createToken($validated['device_name'])->plainTextToken,
                            'user' => [
                                'id' => $user->id,
                                'name' => $user->name,
                                'email' => $user->email,
                                'two_factor_enabled' => true,
                            ],
                        ]))->toArray(request()),
                        201
                    );
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
        if ($validated['wipe_tokens'] ?? false) {
            $user->tokens()->delete();
        }

        return response()->json(
            (new \App\Http\Resources\AuthTokenResource([
                'token' => $user->createToken($validated['device_name'])->plainTextToken,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'two_factor_enabled' => false,
                ],
            ]))->toArray(request()),
            201
        );
    }

    /**
     * Verify two-factor authentication and acquire token.
     *
     * @unauthenticated
     */
    public function verify_two_factor(VerifyTwoFactorMobileAppAuthenticationRequest $request)
    {
        $validated = $request->validated();

        $user = User::where('email', $validated['email'])->first();
        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (! $user->hasTwoFactorEnabled()) {
            throw ValidationException::withMessages([
                'code' => ['Two-factor authentication is not enabled for this account.'],
            ]);
        }

        $method = $validated['method'] ?? $user->getPrimary2faMethod();
        $verified = false;

        // Verify based on method
        switch ($method) {
            case 'totp':
                if ($user->canUseTotpFor2fa()) {
                    try {
                        $decryptedSecret = decrypt($user->two_factor_secret);
                        $verified = app(TwoFactorAuthenticationProvider::class)->verify($decryptedSecret, $validated['code']);
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
                    $verified = $user->verifyEmailTwoFactorCode($validated['code']);
                }
                break;

            default:
                // Try both methods if no specific method requested
                if ($user->canUseTotpFor2fa()) {
                    try {
                        $decryptedSecret = decrypt($user->two_factor_secret);
                        $verified = app(TwoFactorAuthenticationProvider::class)->verify($decryptedSecret, $validated['code']);
                    } catch (\PragmaRX\Google2FA\Exceptions\InvalidCharactersException $e) {
                        // Invalid TOTP secret in database - treat as invalid code
                        $verified = false;
                    } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
                        // Invalid encrypted secret in database - treat as invalid code
                        $verified = false;
                    }
                }

                if (! $verified && $user->canUseEmailFor2fa()) {
                    $verified = $user->verifyEmailTwoFactorCode($validated['code']);
                }
                break;
        }

        if (! $verified) {
            throw ValidationException::withMessages([
                'code' => ['The provided two-factor authentication code was invalid.'],
            ]);
        }

        // 2FA verified, issue token
        if ($validated['wipe_tokens'] ?? false) {
            $user->tokens()->delete();
        }

        return response()->json(
            (new \App\Http\Resources\AuthTokenResource([
                'token' => $user->createToken($validated['device_name'])->plainTextToken,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'two_factor_enabled' => true,
                    'two_factor_method' => $method,
                ],
            ]))->toArray(request()),
            201
        );
    }

    /**
     * Request an email 2FA code for mobile authentication.
     *
     * @unauthenticated
     */
    public function request_email_code(RequestEmailCodeMobileAppAuthenticationRequest $request)
    {
        $validated = $request->validated();

        $user = User::where('email', $validated['email'])->first();
        if (! $user || ! Hash::check($validated['password'], $user->password)) {
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

            return new \App\Http\Resources\EmailCodeRequestResource([
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
    public function two_factor_status(TwoFactorStatusMobileAppAuthenticationRequest $request)
    {
        $validated = $request->validated();

        $user = User::where('email', $validated['email'])->first();
        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        return new \App\Http\Resources\TwoFactorStatusResource([
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

        return response()->json(
            (new \App\Http\Resources\TwoFactorChallengeResource([
                'requires_two_factor' => true,
                'available_methods' => $availableMethods,
                'primary_method' => $primaryMethod,
                'message' => 'Two-factor authentication required. Please provide a verification code.',
            ]))->toArray(request()),
            202
        ); // 202 Accepted - request received but requires additional action
    }

    /**
     * Revoke all the token for the current user.
     */
    public function wipe_tokens(Request $request)
    {
        if (! $request->user()) {
            return response()->json(
                (new \App\Http\Resources\MessageResource(['message' => 'Unauthenticated.']))->toArray(request()),
                401
            );
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
