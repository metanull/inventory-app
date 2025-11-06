<?php

namespace App\Http\Controllers;

class SpaController extends Controller
{
    /**
     * Serve the Vue.js SPA for client-side routing.
     * Works with Apache/.htaccess in production.
     */
    public function index()
    {
        return response()->file(public_path('cli/index.html'));
    }
}
