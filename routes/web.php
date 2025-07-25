<?php

use Dedoc\Scramble\Generator;
use Dedoc\Scramble\Scramble;
use Illuminate\Support\Facades\Route;

// Root redirect to web application
Route::get('/', function () {
    return redirect('/web');
})->name('root');

// Jetstream/Blade web application routes - all under /web prefix
Route::prefix('web')->group(function () {
    // Welcome/homepage for the web application
    Route::get('/', function () {
        return view('welcome');
    })->name('web.welcome');

    Route::middleware([
        'auth:sanctum',
        config('jetstream.auth_session'),
        'verified',
    ])->group(function () {
        Route::get('/dashboard', function () {
            return view('dashboard');
        })->name('dashboard');
    });

    // Add other Jetstream routes here if needed
    // These will automatically be prefixed with /web
});

// Vue.js SPA Route - serves the client app at /cli
Route::get('/cli/{any?}', function () {
    return view('app');
})->where('any', '.*')->name('spa');

Route::get('/api.json', function (Generator $generator) {
    $config = Scramble::getGeneratorConfig('default');

    return response()->json($generator($config), 200, [
        'Cache-Control' => 'public, max-age=3600', // Cache for 1 hour
    ], JSON_PRETTY_PRINT);
})->name('api.documentation');
