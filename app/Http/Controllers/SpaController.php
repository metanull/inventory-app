<?php

namespace App\Http\Controllers;

use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SpaController extends Controller
{
    /**
     * Serve the Vue.js SPA for client-side routing.
     * Works with Apache/.htaccess in production.
     */
    public function index(): BinaryFileResponse
    {
        return response()->file(public_path('cli/index.html'));
    }
}
