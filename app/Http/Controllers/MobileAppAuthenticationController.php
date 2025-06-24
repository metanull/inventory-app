<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

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
            'wipe_tokens' => 'boolean|default:false',
        ]);

        $user = User::where('email', $request->email)->first();
        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }
        if ($request->wipe_tokens) {
            $user->tokens()->delete();
        }

        return response($user->createToken($request->device_name)->plainTextToken, 201);
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
