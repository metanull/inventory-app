<?php

use App\Enums\Permission;
use App\Http\Controllers\Web\AvailableImageController as WebAvailableImageController;
use App\Http\Controllers\Web\CollectionController as WebCollectionController;
use App\Http\Controllers\Web\CollectionImageController as WebCollectionImageController;
use App\Http\Controllers\Web\CollectionTranslationController as WebCollectionTranslationController;
use App\Http\Controllers\Web\ContextController as WebContextController;
use App\Http\Controllers\Web\CountryController as WebCountryController;
use App\Http\Controllers\Web\GlossaryController as WebGlossaryController;
use App\Http\Controllers\Web\GlossarySpellingController as WebGlossarySpellingController;
use App\Http\Controllers\Web\GlossaryTranslationController as WebGlossaryTranslationController;
use App\Http\Controllers\Web\ImageUploadController as WebImageUploadController;
use App\Http\Controllers\Web\ItemController as WebItemController;
use App\Http\Controllers\Web\ItemImageController as WebItemImageController;
use App\Http\Controllers\Web\ItemTranslationController as WebItemTranslationController;
use App\Http\Controllers\Web\LanguageController as WebLanguageController;
use App\Http\Controllers\Web\PartnerController as WebPartnerController;
use App\Http\Controllers\Web\ProjectController as WebProjectController;
use App\Http\Controllers\Web\TagController as WebTagController;
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

    // Authenticated resource management (requires data permissions)
    Route::middleware(['auth', 'permission:'.Permission::VIEW_DATA->value])->group(function () {
        Route::resource('items', WebItemController::class);

        // Item Images - nested routes
        Route::prefix('items/{item}/item-images')->name('items.item-images.')->group(function () {
            Route::get('/create', [WebItemImageController::class, 'create'])->name('create');
            Route::post('/', [WebItemImageController::class, 'store'])->name('store');
            Route::get('/{item_image}/view', [WebItemImageController::class, 'view'])->name('view');
            Route::get('/{item_image}/download', [WebItemImageController::class, 'download'])->name('download');
            Route::get('/{item_image}/edit', [WebItemImageController::class, 'edit'])->name('edit');
            Route::put('/{item_image}', [WebItemImageController::class, 'update'])->name('update');
            Route::post('/{item_image}/move-up', [WebItemImageController::class, 'moveUp'])->name('move-up');
            Route::post('/{item_image}/move-down', [WebItemImageController::class, 'moveDown'])->name('move-down');
            Route::post('/{item_image}/detach', [WebItemImageController::class, 'detach'])->name('detach');
            Route::delete('/{item_image}', [WebItemImageController::class, 'destroy'])->name('destroy');
        });

        // Item Tags - nested routes for attaching/detaching tags
        Route::post('items/{item}/tags', [WebItemController::class, 'attachTag'])->name('items.tags.attach');
        Route::delete('items/{item}/tags/{tag}', [WebItemController::class, 'detachTag'])->name('items.tags.detach');

        Route::resource('item-translations', WebItemTranslationController::class);
        Route::resource('collection-translations', WebCollectionTranslationController::class);
        Route::resource('partners', WebPartnerController::class);
        Route::resource('countries', WebCountryController::class);
        Route::resource('languages', WebLanguageController::class);
        Route::resource('projects', WebProjectController::class);
        Route::resource('contexts', WebContextController::class);
        Route::resource('tags', WebTagController::class);
        Route::resource('glossaries', WebGlossaryController::class);

        // Glossary Translations - nested routes
        Route::prefix('glossaries/{glossary}/translations')->name('glossaries.translations.')->group(function () {
            Route::get('/', [WebGlossaryTranslationController::class, 'index'])->name('index');
            Route::get('/create', [WebGlossaryTranslationController::class, 'create'])->name('create');
            Route::post('/', [WebGlossaryTranslationController::class, 'store'])->name('store');
            Route::get('/{translation}', [WebGlossaryTranslationController::class, 'show'])->name('show');
            Route::get('/{translation}/edit', [WebGlossaryTranslationController::class, 'edit'])->name('edit');
            Route::put('/{translation}', [WebGlossaryTranslationController::class, 'update'])->name('update');
            Route::delete('/{translation}', [WebGlossaryTranslationController::class, 'destroy'])->name('destroy');
        });

        // Glossary Spellings - nested routes
        Route::prefix('glossaries/{glossary}/spellings')->name('glossaries.spellings.')->group(function () {
            Route::get('/', [WebGlossarySpellingController::class, 'index'])->name('index');
            Route::get('/create', [WebGlossarySpellingController::class, 'create'])->name('create');
            Route::post('/', [WebGlossarySpellingController::class, 'store'])->name('store');
            Route::get('/{spelling}', [WebGlossarySpellingController::class, 'show'])->name('show');
            Route::get('/{spelling}/edit', [WebGlossarySpellingController::class, 'edit'])->name('edit');
            Route::put('/{spelling}', [WebGlossarySpellingController::class, 'update'])->name('update');
            Route::delete('/{spelling}', [WebGlossarySpellingController::class, 'destroy'])->name('destroy');
        });

        Route::resource('collections', WebCollectionController::class);
        Route::resource('authors', \App\Http\Controllers\Web\AuthorController::class);
        Route::resource('contacts', \App\Http\Controllers\Web\ContactController::class);
        Route::resource('addresses', \App\Http\Controllers\Web\AddressController::class);

        // Collection Images - nested routes
        Route::prefix('collections/{collection}/collection-images')->name('collections.collection-images.')->group(function () {
            Route::get('/create', [WebCollectionImageController::class, 'create'])->name('create');
            Route::post('/', [WebCollectionImageController::class, 'store'])->name('store');
            Route::get('/{collection_image}/view', [WebCollectionImageController::class, 'view'])->name('view');
            Route::get('/{collection_image}/download', [WebCollectionImageController::class, 'download'])->name('download');
            Route::get('/{collection_image}/edit', [WebCollectionImageController::class, 'edit'])->name('edit');
            Route::put('/{collection_image}', [WebCollectionImageController::class, 'update'])->name('update');
            Route::post('/{collection_image}/move-up', [WebCollectionImageController::class, 'moveUp'])->name('move-up');
            Route::post('/{collection_image}/move-down', [WebCollectionImageController::class, 'moveDown'])->name('move-down');
            Route::post('/{collection_image}/detach', [WebCollectionImageController::class, 'detach'])->name('detach');
            Route::delete('/{collection_image}', [WebCollectionImageController::class, 'destroy'])->name('destroy');
        });

        // Available Images
        Route::get('available-images', [WebAvailableImageController::class, 'index'])->name('available-images.index');
        Route::get('available-images/{availableImage}', [WebAvailableImageController::class, 'show'])->name('available-images.show');
        Route::get('available-images/{availableImage}/view', [WebAvailableImageController::class, 'view'])->name('available-images.view');
        Route::get('available-images/{availableImage}/download', [WebAvailableImageController::class, 'download'])->name('available-images.download');
        Route::get('available-images/{availableImage}/edit', [WebAvailableImageController::class, 'edit'])->name('available-images.edit');
        Route::put('available-images/{availableImage}', [WebAvailableImageController::class, 'update'])->name('available-images.update');
        Route::delete('available-images/{availableImage}', [WebAvailableImageController::class, 'destroy'])->name('available-images.destroy');
    });

    // Image upload routes - require create permission
    Route::middleware(['auth', 'permission:'.Permission::CREATE_DATA->value])->group(function () {
        Route::get('images/upload', [WebImageUploadController::class, 'create'])->name('images.upload');
        Route::post('images/upload', [WebImageUploadController::class, 'store'])->name('images.store');
    });

    // Admin routes - User Management (requires user management permissions)
    Route::prefix('admin')->name('admin.')->middleware(['auth', 'permission:'.Permission::MANAGE_USERS->value])->group(function () {
        Route::resource('users', \App\Http\Controllers\UserManagementController::class);
    });

    // Admin routes - Role Management (requires role management permissions)
    Route::prefix('admin')->name('admin.')->middleware(['auth', 'permission:'.Permission::MANAGE_ROLES->value])->group(function () {
        Route::resource('roles', \App\Http\Controllers\RoleManagementController::class);
        Route::get('roles/{role}/permissions', [\App\Http\Controllers\RoleManagementController::class, 'permissions'])->name('roles.permissions');
        Route::put('roles/{role}/permissions', [\App\Http\Controllers\RoleManagementController::class, 'updatePermissions'])->name('roles.updatePermissions');
    });

    // Settings routes (requires settings management permissions)
    Route::middleware(['auth', 'permission:'.Permission::MANAGE_SETTINGS->value])->group(function () {
        Route::get('/settings', [\App\Http\Controllers\SettingsController::class, 'index'])->name('settings.index');
        Route::put('/settings', [\App\Http\Controllers\SettingsController::class, 'update'])->name('settings.update');
    });
});

// Note: Registration routes are handled by Fortify with self_registration middleware
// See app/Providers/FortifyServiceProvider.php for middleware configuration

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
