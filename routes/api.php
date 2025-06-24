<?php

use App\Http\Controllers\AvailableImageController;
use App\Http\Controllers\ContextController;
use App\Http\Controllers\CountryController;
use App\http\controllers\DetailController;
use App\Http\Controllers\ImageUploadController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\PartnerController;
use App\Http\Controllers\PictureController;
use App\Http\Controllers\ProjectController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Response;
use App\Models\User;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::patch('context/{context}/default', [ContextController::class, 'setDefault'])
    ->name('context.setDefault')
    ->middleware('auth:sanctum');

Route::get('context/default', [ContextController::class, 'getDefault'])
    ->name('context.getDefault')
    ->middleware('auth:sanctum');

Route::patch('language/{language}/default', [LanguageController::class, 'setDefault'])
    ->name('language.setDefault')
    ->middleware('auth:sanctum');

Route::get('language/default', [LanguageController::class, 'getDefault'])
    ->name('language.getDefault')
    ->middleware('auth:sanctum');

Route::patch('project/{project}/launched', [ProjectController::class, 'setLaunched'])
    ->name('project.setLaunched')
    ->middleware('auth:sanctum');

Route::patch('project/{project}/enabled', [ProjectController::class, 'setEnabled'])
    ->name('project.setEnabled')
    ->middleware('auth:sanctum');

Route::resource('country', CountryController::class)->except([
    'create', 'edit',
])->middleware('auth:sanctum');

Route::resource('language', LanguageController::class)->except([
    'create', 'edit',
])->middleware('auth:sanctum');

Route::resource('context', ContextController::class)->except([
    'create', 'edit',
])->middleware('auth:sanctum');

Route::resource('partner', PartnerController::class)->except([
    'create', 'edit',
])->middleware('auth:sanctum');

Route::resource('item', ItemController::class)->except([
    'create', 'edit',
])->middleware('auth:sanctum');

Route::resource('picture', PictureController::class)->except([
    'create', 'edit',
])->middleware('auth:sanctum');

Route::resource('project', ProjectController::class)->except([
    'create', 'edit',
])->middleware('auth:sanctum');

Route::resource('image-upload', ImageUploadController::class)->except([
    'create', 'edit', 'update',
])->middleware('auth:sanctum');

Route::get('available-image/{available_image}/download', [AvailableImageController::class, 'download'])
    ->name('available-image.download')
    ->middleware('auth:sanctum');

Route::resource('available-image', AvailableImageController::class)->except([
    'create', 'edit', 'store',
])->middleware('auth:sanctum');

Route::resource('detail', DetailController::class)->except([
    'create', 'edit',
])->middleware('auth:sanctum');




/**
 * Route to issue mobile API tokens for users.
 * This route allows users to authenticate using their email and password,
 * and receive a token that can be used for mobile API requests.
 * 
 * References: https://laravel.com/docs/12.x/sanctum#issuing-mobile-api-tokens
 */
Route::post('mobile/token', function (Request $request) {
    // return response($request->all());
     $request->validate([
        'email' => 'required|email',
        'password' => 'required',
        'device_name' => 'required',
    ]);
    $user = User::where('email', $request->email)->first();
    if (! $user || ! Hash::check($request->password, $user->password)) {
        throw ValidationException::withMessages([
            'email' => ['The provided credentials are incorrect.'],
        ]);
    }
    return response($user->createToken($request->device_name)->plainTextToken, 201);
})->name('mobile.token');

/**
 * Route to wipe all mobile API tokens for the authenticated user.
 * This route allows users to delete all their existing tokens,
 * effectively logging them out from all devices.
 */
Route::get('mobile/wipe', function (Request $request) {
    // return response($request->all());
    // return response($request->user());
    // return response($request->user()->tokens()->get());
    // return response(User::where('id', $request->user()->id)->first());
    return response(User::where('email', $request->user()->email)->first());

    $request->user()->tokens()->delete();
    return response()->noContent();
})->name('mobile.wipe')
  ->middleware('auth:sanctum');