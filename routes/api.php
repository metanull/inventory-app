<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\LanguageController;
use App\Http\Controllers\ContextController;
use App\Http\Controllers\PartnerController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\ContextItemController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::resource('language', LanguageController::class)->except([
    'create', 'edit'
])->middleware('auth:sanctum');

Route::resource('context', ContextController::class)->except([
    'create', 'edit'
])->middleware('auth:sanctum');

Route::resource('partner', PartnerController::class)->except([
    'create', 'edit'
])->middleware('auth:sanctum');

Route::resource('item', ItemController::class)->except([
    'create', 'edit'
])->middleware('auth:sanctum');

Route::resource('context_item', ContextItemController::class)->except([
    'create', 'edit'
])->middleware('auth:sanctum');