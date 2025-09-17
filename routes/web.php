<?php

use App\Http\Controllers\Web\CountryController as WebCountryController;
use App\Http\Controllers\Web\ItemController as WebItemController;
use App\Http\Controllers\Web\LanguageController as WebLanguageController;
use App\Http\Controllers\Web\PartnerController as WebPartnerController;
use Dedoc\Scramble\Generator;
use Dedoc\Scramble\Scramble;
use Illuminate\Support\Facades\Route;

// Root redirect to web application
Route::get('/', function () {
    return redirect('/web');
})->name('root');

// Jetstream/Blade web application routes - all under /web prefix
Route::prefix('web')->group(function () {
    // Unified root: guest sees marketing welcome, authenticated sees portal tiles
    Route::get('/', function () {
        return view('home');
    })->name('web.welcome');

    // Maintain legacy dashboard route name (Jetstream expectation) -> redirect to unified root
    Route::get('/dashboard', function () {
        return redirect()->route('web.welcome');
    })->name('dashboard');

    // Authenticated resource management
    Route::middleware(['auth'])->group(function () {
        Route::resource('items', WebItemController::class);
        Route::resource('partners', WebPartnerController::class);
        Route::resource('countries', WebCountryController::class);
        Route::resource('languages', WebLanguageController::class);
    });
});

// Vue.js SPA Route - serves the client app at /cli (demo client)
Route::get('/cli/{any?}', function () {
    return view('app');
})->where('any', '.*')->name('spa');

Route::get('/api.json', function (Generator $generator) {
    $config = Scramble::getGeneratorConfig('default');

    return response()->json($generator($config), 200, [
        'Cache-Control' => 'public, max-age=3600', // Cache for 1 hour
    ], JSON_PRETTY_PRINT);
})->name('api.documentation');
