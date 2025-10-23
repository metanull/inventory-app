<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\AcquireTokenMobileAppAuthenticationRequest;
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
        if ($user->hasEnabledTwoFactorAuthentication()) {
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

        if (! $user->hasEnabledTwoFactorAuthentication()) {
            throw ValidationException::withMessages([
                'code' => ['Two-factor authentication is not enabled for this account.'],
            ]);
        }

        // Verify TOTP code
        $verified = false;
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
                    'two_factor_method' => 'totp',
                ],
            ]))->toArray(request()),
            201
        );
    }

    /**
     * Get user's 2FA status.
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

        $has2FA = $user->hasEnabledTwoFactorAuthentication();

        return new \App\Http\Resources\TwoFactorStatusResource([
            'two_factor_enabled' => $has2FA,
            'available_methods' => $has2FA ? ['totp'] : [],
            'primary_method' => $has2FA ? 'totp' : null,
            'requires_two_factor' => $has2FA,
        ]);
    }

    /**
     * Return a 2FA challenge response.
     */
    protected function requireTwoFactorAuthentication(User $user)
    {
        return response()->json(
            (new \App\Http\Resources\TwoFactorChallengeResource([
                'requires_two_factor' => true,
                'available_methods' => ['totp'],
                'primary_method' => 'totp',
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

        // Verify TOTP
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

        return false;
    }
}
