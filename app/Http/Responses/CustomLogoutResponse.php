<?php

namespace App\Http\Responses;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Fortify\Contracts\LogoutResponse as LogoutResponseContract;
use Symfony\Component\HttpFoundation\Response;

class CustomLogoutResponse implements LogoutResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param  Request  $request
     * @return Response
     */
    public function toResponse($request)
    {
        Log::info('CustomLogoutResponse called');

        // Clear 2FA challenge session
        $request->session()->forget('login.id');

        Log::info('Cleared login.id from session');

        return redirect('/');
    }
}
