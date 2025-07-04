<?php

use App\Http\Controllers\Api\MarkdownController;
use App\Http\Controllers\AvailableImageController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ContextController;
use App\Http\Controllers\ContextualizationController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\DetailController;
use App\Http\Controllers\ImageUploadController;
use App\Http\Controllers\InternationalizationController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\MobileAppAuthenticationController;
use App\Http\Controllers\PartnerController;
use App\Http\Controllers\PictureController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\TagItemController;
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

Route::resource('tag', TagController::class)->except([
    'create', 'edit',
])->middleware('auth:sanctum');

Route::get('tag/for-item/{item}', [TagController::class, 'forItem'])
    ->name('tag.forItem')
    ->middleware('auth:sanctum');

Route::resource('tag-item', TagItemController::class)->except([
    'create', 'edit',
])->middleware('auth:sanctum');

Route::resource('partner', PartnerController::class)->except([
    'create', 'edit',
])->middleware('auth:sanctum');

Route::resource('item', ItemController::class)->except([
    'create', 'edit',
])->middleware('auth:sanctum');

Route::get('item/for-tag/{tag}', [ItemController::class, 'forTag'])
    ->name('item.forTag')
    ->middleware('auth:sanctum');

Route::post('item/with-all-tags', [ItemController::class, 'withAllTags'])
    ->name('item.withAllTags')
    ->middleware('auth:sanctum');

Route::post('item/with-any-tags', [ItemController::class, 'withAnyTags'])
    ->name('item.withAnyTags')
    ->middleware('auth:sanctum');

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

Route::resource('contact', ContactController::class)->except([
    'create', 'edit',
])->middleware('auth:sanctum');

// Contextualization routes
Route::get('contextualization/default-context', [ContextualizationController::class, 'defaultContext'])
    ->name('contextualization.defaultContext')
    ->middleware('auth:sanctum');

Route::get('contextualization/for-items', [ContextualizationController::class, 'forItems'])
    ->name('contextualization.forItems')
    ->middleware('auth:sanctum');

Route::get('contextualization/for-details', [ContextualizationController::class, 'forDetails'])
    ->name('contextualization.forDetails')
    ->middleware('auth:sanctum');

Route::post('contextualization/with-default-context', [ContextualizationController::class, 'storeWithDefaultContext'])
    ->name('contextualization.storeWithDefaultContext')
    ->middleware('auth:sanctum');

Route::resource('contextualization', ContextualizationController::class)->except([
    'create', 'edit',
])->middleware('auth:sanctum');

// Internationalization routes
Route::get('internationalization/default-language', [InternationalizationController::class, 'inDefaultLanguage'])
    ->name('internationalization.inDefaultLanguage')
    ->middleware('auth:sanctum');

Route::get('internationalization/english', [InternationalizationController::class, 'inEnglish'])
    ->name('internationalization.inEnglish')
    ->middleware('auth:sanctum');

Route::resource('internationalization', InternationalizationController::class)->except([
    'create', 'edit',
])->middleware('auth:sanctum');

Route::post('mobile/acquire-token', [MobileAppAuthenticationController::class, 'acquire_token'])
    ->name('token.acquire');

Route::get('mobile/wipe', [MobileAppAuthenticationController::class, 'wipe_tokens'])
    ->name('token.wipe')
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
