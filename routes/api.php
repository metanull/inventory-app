<?php

use App\Http\Controllers\AddressController;
use App\Http\Controllers\AddressTranslationController;
use App\Http\Controllers\Api\MarkdownController;
use App\Http\Controllers\AvailableImageController;
use App\Http\Controllers\CollectionController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ContactTranslationController;
use App\Http\Controllers\ContextController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\ImageUploadController;
use App\Http\Controllers\InfoController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\ItemImageController;
use App\Http\Controllers\ItemTranslationController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\LocationTranslationController;
use App\Http\Controllers\MobileAppAuthenticationController;
use App\Http\Controllers\PartnerController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProvinceController;
use App\Http\Controllers\ProvinceTranslationController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\ThemeController;
use App\Http\Controllers\ThemeTranslationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public info endpoints for monitoring and API information
Route::get('/info', [InfoController::class, 'index'])->name('info.index');
Route::get('/health', [InfoController::class, 'health'])->name('info.health');
Route::get('/version', [InfoController::class, 'version'])->name('info.version');

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::patch('context/{context}/default', [ContextController::class, 'setDefault'])
    ->name('context.setDefault')
    ->middleware('auth:sanctum');

Route::delete('context/default', [ContextController::class, 'clearDefault'])
    ->name('context.clearDefault')
    ->middleware('auth:sanctum');

Route::get('context/default', [ContextController::class, 'getDefault'])
    ->name('context.getDefault')
    ->middleware('auth:sanctum');

Route::patch('language/{language}/default', [LanguageController::class, 'setDefault'])
    ->name('language.setDefault')
    ->middleware('auth:sanctum');

Route::delete('language/default', [LanguageController::class, 'clearDefault'])
    ->name('language.clearDefault')
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

Route::resource('tag', TagController::class)->except([
    'create', 'edit',
])->middleware('auth:sanctum');

Route::get('tag/for-item/{item}', [TagController::class, 'forItem'])
    ->name('tag.forItem')
    ->middleware('auth:sanctum');

Route::resource('partner', PartnerController::class)->except([
    'create', 'edit',
])->middleware('auth:sanctum');

Route::resource('item', ItemController::class)->except([
    'create', 'edit',
])->middleware('auth:sanctum');

Route::patch('item/{item}/tags', [ItemController::class, 'updateTags'])
    ->name('item.updateTags')
    ->middleware('auth:sanctum');

Route::get('item/for-tag/{tag}', [ItemController::class, 'forTag'])
    ->name('item.forTag')
    ->middleware('auth:sanctum');

Route::post('item/with-all-tags', [ItemController::class, 'withAllTags'])
    ->name('item.withAllTags')
    ->middleware('auth:sanctum');

Route::post('item/with-any-tags', [ItemController::class, 'withAnyTags'])
    ->name('item.withAnyTags')
    ->middleware('auth:sanctum');

// New routes for item types and hierarchical relationships
Route::get('item/type/{type}', [ItemController::class, 'byType'])
    ->name('item.byType')
    ->middleware('auth:sanctum');

Route::get('item/parents', [ItemController::class, 'parents'])
    ->name('item.parents')
    ->middleware('auth:sanctum');

Route::get('item/children', [ItemController::class, 'children'])
    ->name('item.children')
    ->middleware('auth:sanctum');

// ItemImage routes - nested under items
Route::get('item/{item}/images', [ItemImageController::class, 'index'])
    ->name('item.images.index')
    ->middleware('auth:sanctum');

Route::post('item/{item}/images', [ItemImageController::class, 'store'])
    ->name('item.images.store')
    ->middleware('auth:sanctum');

Route::get('item-image/{itemImage}', [ItemImageController::class, 'show'])
    ->name('item-image.show')
    ->middleware('auth:sanctum');

Route::patch('item-image/{itemImage}', [ItemImageController::class, 'update'])
    ->name('item-image.update')
    ->middleware('auth:sanctum');

Route::delete('item-image/{itemImage}', [ItemImageController::class, 'destroy'])
    ->name('item-image.destroy')
    ->middleware('auth:sanctum');

Route::patch('item-image/{itemImage}/move-up', [ItemImageController::class, 'moveUp'])
    ->name('item-image.moveUp')
    ->middleware('auth:sanctum');

Route::patch('item-image/{itemImage}/move-down', [ItemImageController::class, 'moveDown'])
    ->name('item-image.moveDown')
    ->middleware('auth:sanctum');

Route::patch('item-image/{itemImage}/tighten-ordering', [ItemImageController::class, 'tightenOrdering'])
    ->name('item-image.tightenOrdering')
    ->middleware('auth:sanctum');

Route::post('item/{item}/attach-image', [ItemImageController::class, 'attachFromAvailable'])
    ->name('item.attachImage')
    ->middleware('auth:sanctum');

Route::post('item-image/{itemImage}/detach', [ItemImageController::class, 'detachToAvailable'])
    ->name('item-image.detach')
    ->middleware('auth:sanctum');

Route::resource('project', ProjectController::class)->except([
    'create', 'edit',
])->middleware('auth:sanctum');

Route::resource('image-upload', ImageUploadController::class)->except([
    'create', 'edit', 'update',
])->middleware('auth:sanctum');

Route::get('image-upload/{id}/status', [ImageUploadController::class, 'status'])
    ->name('image-upload.status')
    ->middleware('auth:sanctum');

Route::get('available-image/{available_image}/download', [AvailableImageController::class, 'download'])
    ->name('available-image.download')
    ->middleware('auth:sanctum');

Route::get('available-image/{available_image}/view', [AvailableImageController::class, 'view'])
    ->name('available-image.view')
    ->middleware('auth:sanctum');

Route::resource('available-image', AvailableImageController::class)->except([
    'create', 'edit', 'store',
])->middleware('auth:sanctum');

Route::resource('contact', ContactController::class)->except([
    'create', 'edit',
])->middleware('auth:sanctum');

Route::resource('province', ProvinceController::class)->except([
    'create', 'edit',
])->middleware('auth:sanctum');

Route::resource('location', LocationController::class)->except([
    'create', 'edit',
])->middleware('auth:sanctum');

Route::resource('address', AddressController::class)->except([
    'create', 'edit',
])->middleware('auth:sanctum');

Route::resource('contact-translation', ContactTranslationController::class)->except([
    'create', 'edit',
])->middleware('auth:sanctum');

Route::resource('province-translation', ProvinceTranslationController::class)->except([
    'create', 'edit',
])->middleware('auth:sanctum');

Route::resource('location-translation', LocationTranslationController::class)->except([
    'create', 'edit',
])->middleware('auth:sanctum');

Route::resource('address-translation', AddressTranslationController::class)->except([
    'create', 'edit',
])->middleware('auth:sanctum');

Route::resource('item-translation', ItemTranslationController::class)->except([
    'create', 'edit',
])->middleware('auth:sanctum');

Route::post('mobile/acquire-token', [MobileAppAuthenticationController::class, 'acquire_token'])
    ->name('token.acquire');

Route::post('mobile/verify-two-factor', [MobileAppAuthenticationController::class, 'verify_two_factor'])
    ->name('token.verify_two_factor');

Route::post('mobile/request-email-code', [MobileAppAuthenticationController::class, 'request_email_code'])
    ->name('token.request_email_code');

Route::post('mobile/two-factor-status', [MobileAppAuthenticationController::class, 'two_factor_status'])
    ->name('token.two_factor_status');

Route::get('mobile/wipe', [MobileAppAuthenticationController::class, 'wipe_tokens'])
    ->name('token.wipe')
    ->middleware('auth:sanctum');

Route::resource('collection', CollectionController::class)->except([
    'create', 'edit',
])->middleware('auth:sanctum');

// New routes for collection types and item management
Route::get('collection/type/{type}', [CollectionController::class, 'byType'])
    ->name('collection.byType')
    ->middleware('auth:sanctum');

Route::post('collection/{collection}/attach-item', [CollectionController::class, 'attachItem'])
    ->name('collection.attachItem')
    ->middleware('auth:sanctum');

Route::delete('collection/{collection}/detach-item', [CollectionController::class, 'detachItem'])
    ->name('collection.detachItem')
    ->middleware('auth:sanctum');

Route::post('collection/{collection}/attach-items', [CollectionController::class, 'attachItems'])
    ->name('collection.attachItems')
    ->middleware('auth:sanctum');

Route::delete('collection/{collection}/detach-items', [CollectionController::class, 'detachItems'])
    ->name('collection.detachItems')
    ->middleware('auth:sanctum');

// Markdown conversion routes
Route::prefix('markdown')->group(function () {
    Route::post('to-html', [MarkdownController::class, 'markdownToHtml'])
        ->name('markdown.toHtml');

    Route::post('from-html', [MarkdownController::class, 'htmlToMarkdown'])
        ->name('markdown.fromHtml');

    Route::post('validate', [MarkdownController::class, 'validateMarkdown'])
        ->name('markdown.validate');

    Route::post('preview', [MarkdownController::class, 'previewMarkdown'])
        ->name('markdown.preview');

    Route::post('is-markdown', [MarkdownController::class, 'isMarkdown'])
        ->name('markdown.isMarkdown');

    Route::get('allowed-elements', [MarkdownController::class, 'getAllowedElements'])
        ->name('markdown.allowedElements');
});

Route::resource('theme', ThemeController::class)->except([
    'create', 'edit',
])->middleware('auth:sanctum');

Route::resource('theme-translation', ThemeTranslationController::class)->except([
    'create', 'edit',
])->middleware('auth:sanctum');
