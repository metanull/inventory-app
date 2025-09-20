<?php

namespace App\Http\Controllers;

use App\Http\Requests\MobileAppAuthentication\AcquireTokenRequest;
use App\Http\Requests\MobileAppAuthentication\WipeTokensRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class MobileAppAuthenticationController extends Controller
{
    /**
     * Acquire a token for the user.
     *
     * @unauthenticated
     */
    public function acquire_token(AcquireTokenRequest $request)
    {
        $validated = $request->validated();

        $user = User::where('email', $validated['email'])->first();
        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }
        if ($request->boolean('wipe_tokens', false)) {
            $user->tokens()->delete();
        }

        return response($user->createToken($validated['device_name'])->plainTextToken, 201);
    }

    /**
     * Revoke all the token for the current user.
     */
    public function wipe_tokens(WipeTokensRequest $request)
    {
        if (! $request->user()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }
        $request->user()->tokens()->delete();

        return response()->noContent();
    }
}
