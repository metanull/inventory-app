<?php

use App\Http\Controllers\AvailableImageController;
use App\Http\Controllers\ContextController;
use App\Http\Controllers\CountryController;
use App\http\controllers\DetailController;
use App\Http\Controllers\ImageUploadController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\MobileAppAuthenticationController;
use App\Http\Controllers\PartnerController;
use App\Http\Controllers\PictureController;
use App\Http\Controllers\ProjectController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::get('language/english', [LanguageController::class, 'getEnglish'])
    ->name('language.getEnglish')
    ->middleware('auth:sanctum');

Route::patch('project/{project}/launched', [ProjectController::class, 'setLaunched'])
    ->name('project.setLaunched')
    ->middleware('auth:sanctum');

Route::patch('project/{project}/enabled', [ProjectController::class, 'setEnabled'])
    ->name('project.setEnabled')
    ->middleware('auth:sanctum');

Route::get('project/enabled', [ProjectController::class, 'enabled'])
    ->name('project.enabled')
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

Route::post('mobile/acquire-token', [MobileAppAuthenticationController::class, 'acquire_token'])
    ->name('token.acquire');

Route::get('mobile/wipe', [MobileAppAuthenticationController::class, 'wipe_tokens'])
    ->name('token.wipe')
    ->middleware('auth:sanctum');
