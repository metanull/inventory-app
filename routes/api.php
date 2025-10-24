<?php

use App\Enums\Permission;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\AddressTranslationController;
use App\Http\Controllers\Api\MarkdownController;
use App\Http\Controllers\AvailableImageController;
use App\Http\Controllers\CollectionController;
use App\Http\Controllers\CollectionImageController;
use App\Http\Controllers\CollectionTranslationController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ContactTranslationController;
use App\Http\Controllers\ContextController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\GlossaryController;
use App\Http\Controllers\GlossarySpellingController;
use App\Http\Controllers\GlossaryTranslationController;
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
use App\Http\Controllers\PartnerImageController;
use App\Http\Controllers\PartnerTranslationController;
use App\Http\Controllers\PartnerTranslationImageController;
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

Route::get('/user/permissions', [\App\Http\Controllers\UserPermissionsController::class, 'index'])
    ->name('user.permissions')
    ->middleware('auth:sanctum');

// Data operation routes - require appropriate permissions based on HTTP method
Route::middleware(['auth:sanctum'])->group(function () {
    // READ operations - require VIEW_DATA permission
    Route::middleware(['permission:'.Permission::VIEW_DATA->value])->group(function () {
        // Context routes (read)
        Route::get('context/default', [ContextController::class, 'getDefault'])->name('context.getDefault');
        Route::get('context/{context}', [ContextController::class, 'show'])->name('context.show');
        Route::get('context', [ContextController::class, 'index'])->name('context.index');

        // Language routes (read)
        Route::get('language/default', [LanguageController::class, 'getDefault'])->name('language.getDefault');
        Route::get('language/english', [LanguageController::class, 'getEnglish'])->name('language.getEnglish');
        Route::get('language/{language}', [LanguageController::class, 'show'])->name('language.show');
        Route::get('language', [LanguageController::class, 'index'])->name('language.index');

        // Glossary routes (read)
        Route::get('glossary/{glossary}', [GlossaryController::class, 'show'])->name('glossary.show');
        Route::get('glossary', [GlossaryController::class, 'index'])->name('glossary.index');
        Route::get('glossary-translation/{glossaryTranslation}', [GlossaryTranslationController::class, 'show'])->name('glossary-translation.show');
        Route::get('glossary-translation', [GlossaryTranslationController::class, 'index'])->name('glossary-translation.index');
        Route::get('glossary-spelling/{glossarySpelling}', [GlossarySpellingController::class, 'show'])->name('glossary-spelling.show');
        Route::get('glossary-spelling', [GlossarySpellingController::class, 'index'])->name('glossary-spelling.index');

        // Project routes (read)
        Route::get('project/enabled', [ProjectController::class, 'enabled'])->name('project.enabled');
        Route::get('project/{project}', [ProjectController::class, 'show'])->name('project.show');
        Route::get('project', [ProjectController::class, 'index'])->name('project.index');

        // Country routes (read)
        Route::get('country/{country}', [CountryController::class, 'show'])->name('country.show');
        Route::get('country', [CountryController::class, 'index'])->name('country.index');

        // Tag routes (read)
        Route::get('tag/for-item/{item}', [TagController::class, 'forItem'])->name('tag.forItem');
        Route::get('tag/{tag}', [TagController::class, 'show'])->name('tag.show');
        Route::get('tag', [TagController::class, 'index'])->name('tag.index');

        // Partner routes (read)
        Route::get('partner/{partner}', [PartnerController::class, 'show'])->name('partner.show');
        Route::get('partner', [PartnerController::class, 'index'])->name('partner.index');

        // Partner Translation routes (read)
        Route::get('partner-translation/{partnerTranslation}', [PartnerTranslationController::class, 'show'])->name('partner-translation.show');
        Route::get('partner-translation', [PartnerTranslationController::class, 'index'])->name('partner-translation.index');

        // Partner Image routes (read)
        Route::get('partner-image/{partnerImage}', [PartnerImageController::class, 'show'])->name('partner-image.show');
        Route::get('partner-image', [PartnerImageController::class, 'index'])->name('partner-image.index');

        // Partner Translation Image routes (read)
        Route::get('partner-translation-image/{partnerTranslationImage}', [PartnerTranslationImageController::class, 'show'])->name('partner-translation-image.show');
        Route::get('partner-translation-image', [PartnerTranslationImageController::class, 'index'])->name('partner-translation-image.index');

        // Item routes (read)
        Route::get('item/for-tag/{tag}', [ItemController::class, 'forTag'])->name('item.forTag');
        Route::post('item/with-all-tags', [ItemController::class, 'withAllTags'])->name('item.withAllTags');
        Route::post('item/with-any-tags', [ItemController::class, 'withAnyTags'])->name('item.withAnyTags');
        Route::get('item/type/{type}', [ItemController::class, 'byType'])->name('item.byType');
        Route::get('item/parents', [ItemController::class, 'parents'])->name('item.parents');
        Route::get('item/children', [ItemController::class, 'children'])->name('item.children');
        Route::get('item/{item}', [ItemController::class, 'show'])->name('item.show');
        Route::get('item', [ItemController::class, 'index'])->name('item.index');

        // ItemImage routes (read)
        Route::get('item/{item}/images', [ItemImageController::class, 'index'])->name('item.images.index');
        Route::get('item-image/{itemImage}/download', [ItemImageController::class, 'download'])->name('item-image.download');
        Route::get('item-image/{itemImage}/view', [ItemImageController::class, 'view'])->name('item-image.view');
        Route::get('item-image/{itemImage}', [ItemImageController::class, 'show'])->name('item-image.show');

        // Image upload routes (read)
        Route::get('image-upload/{id}/status', [ImageUploadController::class, 'status'])->name('image-upload.status');
        Route::get('image-upload/{image_upload}', [ImageUploadController::class, 'show'])->name('image-upload.show');
        Route::get('image-upload', [ImageUploadController::class, 'index'])->name('image-upload.index');

        // Available image routes (read)
        Route::get('available-image/{available_image}/download', [AvailableImageController::class, 'download'])->name('available-image.download');
        Route::get('available-image/{available_image}/view', [AvailableImageController::class, 'view'])->name('available-image.view');
        Route::get('available-image/{available_image}', [AvailableImageController::class, 'show'])->name('available-image.show');
        Route::get('available-image', [AvailableImageController::class, 'index'])->name('available-image.index');

        // Contact, Location, Address routes (read)
        Route::get('contact/{contact}', [ContactController::class, 'show'])->name('contact.show');
        Route::get('contact', [ContactController::class, 'index'])->name('contact.index');
        Route::get('province/{province}', [ProvinceController::class, 'show'])->name('province.show');
        Route::get('province', [ProvinceController::class, 'index'])->name('province.index');
        Route::get('location/{location}', [LocationController::class, 'show'])->name('location.show');
        Route::get('location', [LocationController::class, 'index'])->name('location.index');
        Route::get('address/{address}', [AddressController::class, 'show'])->name('address.show');
        Route::get('address', [AddressController::class, 'index'])->name('address.index');

        // Translation routes (read)
        Route::get('contact-translation/{contact_translation}', [ContactTranslationController::class, 'show'])->name('contact-translation.show');
        Route::get('contact-translation', [ContactTranslationController::class, 'index'])->name('contact-translation.index');
        Route::get('province-translation/{province_translation}', [ProvinceTranslationController::class, 'show'])->name('province-translation.show');
        Route::get('province-translation', [ProvinceTranslationController::class, 'index'])->name('province-translation.index');
        Route::get('location-translation/{location_translation}', [LocationTranslationController::class, 'show'])->name('location-translation.show');
        Route::get('location-translation', [LocationTranslationController::class, 'index'])->name('location-translation.index');
        Route::get('address-translation/{address_translation}', [AddressTranslationController::class, 'show'])->name('address-translation.show');
        Route::get('address-translation', [AddressTranslationController::class, 'index'])->name('address-translation.index');
        Route::get('item-translation/{item_translation}', [ItemTranslationController::class, 'show'])->name('item-translation.show');
        Route::get('item-translation', [ItemTranslationController::class, 'index'])->name('item-translation.index');
        Route::get('collection-translation/{collection_translation}', [CollectionTranslationController::class, 'show'])->name('collection-translation.show');
        Route::get('collection-translation', [CollectionTranslationController::class, 'index'])->name('collection-translation.index');

        // Collection routes (read)
        Route::get('collection/type/{type}', [CollectionController::class, 'byType'])->name('collection.byType');
        Route::get('collection/{collection}', [CollectionController::class, 'show'])->name('collection.show');
        Route::get('collection', [CollectionController::class, 'index'])->name('collection.index');

        // CollectionImage routes (read)
        Route::get('collection/{collection}/images', [CollectionImageController::class, 'index'])->name('collection.images.index');
        Route::get('collection-image/{collectionImage}/download', [CollectionImageController::class, 'download'])->name('collection-image.download');
        Route::get('collection-image/{collectionImage}/view', [CollectionImageController::class, 'view'])->name('collection-image.view');
        Route::get('collection-image/{collectionImage}', [CollectionImageController::class, 'show'])->name('collection-image.show');

        // Theme routes (read)
        Route::get('theme/{theme}', [ThemeController::class, 'show'])->name('theme.show');
        Route::get('theme', [ThemeController::class, 'index'])->name('theme.index');
        Route::get('theme-translation/{theme_translation}', [ThemeTranslationController::class, 'show'])->name('theme-translation.show');
        Route::get('theme-translation', [ThemeTranslationController::class, 'index'])->name('theme-translation.index');
    });

    // CREATE operations - require CREATE_DATA permission
    Route::middleware(['permission:'.Permission::CREATE_DATA->value])->group(function () {
        Route::post('context', [ContextController::class, 'store'])->name('context.store');
        Route::post('language', [LanguageController::class, 'store'])->name('language.store');
        Route::post('glossary', [GlossaryController::class, 'store'])->name('glossary.store');
        Route::post('glossary/{glossary}/attach-synonym', [GlossaryController::class, 'attachSynonym'])->name('glossary.attachSynonym');
        Route::post('glossary-translation', [GlossaryTranslationController::class, 'store'])->name('glossary-translation.store');
        Route::post('glossary-spelling', [GlossarySpellingController::class, 'store'])->name('glossary-spelling.store');
        Route::post('country', [CountryController::class, 'store'])->name('country.store');
        Route::post('tag', [TagController::class, 'store'])->name('tag.store');
        Route::post('partner', [PartnerController::class, 'store'])->name('partner.store');
        Route::post('partner-translation', [PartnerTranslationController::class, 'store'])->name('partner-translation.store');
        Route::post('partner-image', [PartnerImageController::class, 'store'])->name('partner-image.store');
        Route::post('partner-translation-image', [PartnerTranslationImageController::class, 'store'])->name('partner-translation-image.store');
        Route::post('item', [ItemController::class, 'store'])->name('item.store');
        Route::post('item/{item}/images', [ItemImageController::class, 'store'])->name('item.images.store');
        Route::post('item/{item}/attach-image', [ItemImageController::class, 'attachFromAvailable'])->name('item.attachImage');
        Route::post('item/{item}/attach-tag', [ItemController::class, 'attachTag'])->name('item.attachTag');
        Route::post('item/{item}/attach-tags', [ItemController::class, 'attachTags'])->name('item.attachTags');
        Route::post('project', [ProjectController::class, 'store'])->name('project.store');
        Route::post('image-upload', [ImageUploadController::class, 'store'])->name('image-upload.store');
        Route::post('contact', [ContactController::class, 'store'])->name('contact.store');
        Route::post('province', [ProvinceController::class, 'store'])->name('province.store');
        Route::post('location', [LocationController::class, 'store'])->name('location.store');
        Route::post('address', [AddressController::class, 'store'])->name('address.store');
        Route::post('contact-translation', [ContactTranslationController::class, 'store'])->name('contact-translation.store');
        Route::post('province-translation', [ProvinceTranslationController::class, 'store'])->name('province-translation.store');
        Route::post('location-translation', [LocationTranslationController::class, 'store'])->name('location-translation.store');
        Route::post('address-translation', [AddressTranslationController::class, 'store'])->name('address-translation.store');
        Route::post('item-translation', [ItemTranslationController::class, 'store'])->name('item-translation.store');
        Route::post('collection-translation', [CollectionTranslationController::class, 'store'])->name('collection-translation.store');
        Route::post('collection', [CollectionController::class, 'store'])->name('collection.store');
        Route::post('collection/{collection}/attach-item', [CollectionController::class, 'attachItem'])->name('collection.attachItem');
        Route::post('collection/{collection}/attach-items', [CollectionController::class, 'attachItems'])->name('collection.attachItems');
        Route::post('collection/{collection}/images', [CollectionImageController::class, 'store'])->name('collection.images.store');
        Route::post('collection/{collection}/attach-image', [CollectionImageController::class, 'attachFromAvailable'])->name('collection.attachImage');
        Route::post('theme', [ThemeController::class, 'store'])->name('theme.store');
        Route::post('theme-translation', [ThemeTranslationController::class, 'store'])->name('theme-translation.store');
    });

    // UPDATE operations - require UPDATE_DATA permission
    Route::middleware(['permission:'.Permission::UPDATE_DATA->value])->group(function () {
        Route::patch('context/{context}/default', [ContextController::class, 'setDefault'])->name('context.setDefault');
        Route::delete('context/default', [ContextController::class, 'clearDefault'])->name('context.clearDefault');
        Route::patch('context/{context}', [ContextController::class, 'update'])->name('context.update');
        Route::put('context/{context}', [ContextController::class, 'update']);

        Route::patch('language/{language}/default', [LanguageController::class, 'setDefault'])->name('language.setDefault');
        Route::delete('language/default', [LanguageController::class, 'clearDefault'])->name('language.clearDefault');
        Route::patch('language/{language}', [LanguageController::class, 'update'])->name('language.update');
        Route::put('language/{language}', [LanguageController::class, 'update']);

        Route::patch('glossary/{glossary}', [GlossaryController::class, 'update'])->name('glossary.update');
        Route::put('glossary/{glossary}', [GlossaryController::class, 'update']);

        Route::patch('glossary-translation/{glossaryTranslation}', [GlossaryTranslationController::class, 'update'])->name('glossary-translation.update');
        Route::put('glossary-translation/{glossaryTranslation}', [GlossaryTranslationController::class, 'update']);

        Route::patch('glossary-spelling/{glossarySpelling}', [GlossarySpellingController::class, 'update'])->name('glossary-spelling.update');
        Route::put('glossary-spelling/{glossarySpelling}', [GlossarySpellingController::class, 'update']);

        Route::patch('project/{project}/launched', [ProjectController::class, 'setLaunched'])->name('project.setLaunched');
        Route::patch('project/{project}/enabled', [ProjectController::class, 'setEnabled'])->name('project.setEnabled');
        Route::patch('project/{project}', [ProjectController::class, 'update'])->name('project.update');
        Route::put('project/{project}', [ProjectController::class, 'update']);

        Route::patch('country/{country}', [CountryController::class, 'update'])->name('country.update');
        Route::put('country/{country}', [CountryController::class, 'update']);

        Route::patch('tag/{tag}', [TagController::class, 'update'])->name('tag.update');
        Route::put('tag/{tag}', [TagController::class, 'update']);

        Route::patch('partner/{partner}', [PartnerController::class, 'update'])->name('partner.update');
        Route::put('partner/{partner}', [PartnerController::class, 'update']);

        Route::patch('partner-translation/{partnerTranslation}', [PartnerTranslationController::class, 'update'])->name('partner-translation.update');
        Route::put('partner-translation/{partnerTranslation}', [PartnerTranslationController::class, 'update']);

        Route::patch('partner-image/{partnerImage}', [PartnerImageController::class, 'update'])->name('partner-image.update');
        Route::put('partner-image/{partnerImage}', [PartnerImageController::class, 'update']);

        Route::patch('partner-translation-image/{partnerTranslationImage}', [PartnerTranslationImageController::class, 'update'])->name('partner-translation-image.update');
        Route::put('partner-translation-image/{partnerTranslationImage}', [PartnerTranslationImageController::class, 'update']);

        Route::patch('item/{item}/tags', [ItemController::class, 'updateTags'])->name('item.updateTags');
        Route::patch('item/{item}', [ItemController::class, 'update'])->name('item.update');
        Route::put('item/{item}', [ItemController::class, 'update']);

        Route::patch('item-image/{itemImage}', [ItemImageController::class, 'update'])->name('item-image.update');
        Route::put('item-image/{itemImage}', [ItemImageController::class, 'update']);
        Route::patch('item-image/{itemImage}/move-up', [ItemImageController::class, 'moveUp'])->name('item-image.moveUp');
        Route::patch('item-image/{itemImage}/move-down', [ItemImageController::class, 'moveDown'])->name('item-image.moveDown');
        Route::patch('item-image/{itemImage}/tighten-ordering', [ItemImageController::class, 'tightenOrdering'])->name('item-image.tightenOrdering');

        Route::patch('collection-image/{collectionImage}', [CollectionImageController::class, 'update'])->name('collection-image.update');
        Route::put('collection-image/{collectionImage}', [CollectionImageController::class, 'update']);
        Route::patch('collection-image/{collectionImage}/move-up', [CollectionImageController::class, 'moveUp'])->name('collection-image.moveUp');
        Route::patch('collection-image/{collectionImage}/move-down', [CollectionImageController::class, 'moveDown'])->name('collection-image.moveDown');
        Route::patch('collection-image/{collectionImage}/tighten-ordering', [CollectionImageController::class, 'tightenOrdering'])->name('collection-image.tightenOrdering');

        Route::patch('contact/{contact}', [ContactController::class, 'update'])->name('contact.update');
        Route::put('contact/{contact}', [ContactController::class, 'update']);

        Route::patch('province/{province}', [ProvinceController::class, 'update'])->name('province.update');
        Route::put('province/{province}', [ProvinceController::class, 'update']);

        Route::patch('location/{location}', [LocationController::class, 'update'])->name('location.update');
        Route::put('location/{location}', [LocationController::class, 'update']);

        Route::patch('address/{address}', [AddressController::class, 'update'])->name('address.update');
        Route::put('address/{address}', [AddressController::class, 'update']);

        Route::patch('contact-translation/{contact_translation}', [ContactTranslationController::class, 'update'])->name('contact-translation.update');
        Route::put('contact-translation/{contact_translation}', [ContactTranslationController::class, 'update']);

        Route::patch('province-translation/{province_translation}', [ProvinceTranslationController::class, 'update'])->name('province-translation.update');
        Route::put('province-translation/{province_translation}', [ProvinceTranslationController::class, 'update']);

        Route::patch('location-translation/{location_translation}', [LocationTranslationController::class, 'update'])->name('location-translation.update');
        Route::put('location-translation/{location_translation}', [LocationTranslationController::class, 'update']);

        Route::patch('address-translation/{address_translation}', [AddressTranslationController::class, 'update'])->name('address-translation.update');
        Route::put('address-translation/{address_translation}', [AddressTranslationController::class, 'update']);

        Route::patch('item-translation/{item_translation}', [ItemTranslationController::class, 'update'])->name('item-translation.update');
        Route::put('item-translation/{item_translation}', [ItemTranslationController::class, 'update']);

        Route::patch('collection-translation/{collection_translation}', [CollectionTranslationController::class, 'update'])->name('collection-translation.update');
        Route::put('collection-translation/{collection_translation}', [CollectionTranslationController::class, 'update']);

        Route::patch('collection/{collection}', [CollectionController::class, 'update'])->name('collection.update');
        Route::put('collection/{collection}', [CollectionController::class, 'update']);

        Route::patch('theme/{theme}', [ThemeController::class, 'update'])->name('theme.update');
        Route::put('theme/{theme}', [ThemeController::class, 'update']);

        Route::patch('theme-translation/{theme_translation}', [ThemeTranslationController::class, 'update'])->name('theme-translation.update');
        Route::put('theme-translation/{theme_translation}', [ThemeTranslationController::class, 'update']);
    });

    // DELETE operations - require DELETE_DATA permission
    Route::middleware(['permission:'.Permission::DELETE_DATA->value])->group(function () {
        Route::delete('context/{context}', [ContextController::class, 'destroy'])->name('context.destroy');
        Route::delete('language/{language}', [LanguageController::class, 'destroy'])->name('language.destroy');
        Route::delete('glossary/{glossary}', [GlossaryController::class, 'destroy'])->name('glossary.destroy');
        Route::delete('glossary/{glossary}/detach-synonym', [GlossaryController::class, 'detachSynonym'])->name('glossary.detachSynonym');
        Route::delete('glossary-translation/{glossaryTranslation}', [GlossaryTranslationController::class, 'destroy'])->name('glossary-translation.destroy');
        Route::delete('glossary-spelling/{glossarySpelling}', [GlossarySpellingController::class, 'destroy'])->name('glossary-spelling.destroy');
        Route::delete('country/{country}', [CountryController::class, 'destroy'])->name('country.destroy');
        Route::delete('tag/{tag}', [TagController::class, 'destroy'])->name('tag.destroy');
        Route::delete('partner/{partner}', [PartnerController::class, 'destroy'])->name('partner.destroy');
        Route::delete('partner-translation/{partnerTranslation}', [PartnerTranslationController::class, 'destroy'])->name('partner-translation.destroy');
        Route::delete('partner-image/{partnerImage}', [PartnerImageController::class, 'destroy'])->name('partner-image.destroy');
        Route::delete('partner-translation-image/{partnerTranslationImage}', [PartnerTranslationImageController::class, 'destroy'])->name('partner-translation-image.destroy');
        Route::delete('item/{item}', [ItemController::class, 'destroy'])->name('item.destroy');
        Route::delete('item/{item}/detach-tag', [ItemController::class, 'detachTag'])->name('item.detachTag');
        Route::delete('item/{item}/detach-tags', [ItemController::class, 'detachTags'])->name('item.detachTags');
        Route::delete('item-image/{itemImage}', [ItemImageController::class, 'destroy'])->name('item-image.destroy');
        Route::post('item-image/{itemImage}/detach', [ItemImageController::class, 'detachToAvailable'])->name('item-image.detach');
        Route::delete('collection-image/{collectionImage}', [CollectionImageController::class, 'destroy'])->name('collection-image.destroy');
        Route::post('collection-image/{collectionImage}/detach', [CollectionImageController::class, 'detachToAvailable'])->name('collection-image.detach');
        Route::delete('project/{project}', [ProjectController::class, 'destroy'])->name('project.destroy');
        Route::delete('image-upload/{image_upload}', [ImageUploadController::class, 'destroy'])->name('image-upload.destroy');
        Route::delete('available-image/{available_image}', [AvailableImageController::class, 'destroy'])->name('available-image.destroy');
        Route::patch('available-image/{available_image}', [AvailableImageController::class, 'update'])->name('available-image.update');
        Route::put('available-image/{available_image}', [AvailableImageController::class, 'update']);
        Route::delete('contact/{contact}', [ContactController::class, 'destroy'])->name('contact.destroy');
        Route::delete('province/{province}', [ProvinceController::class, 'destroy'])->name('province.destroy');
        Route::delete('location/{location}', [LocationController::class, 'destroy'])->name('location.destroy');
        Route::delete('address/{address}', [AddressController::class, 'destroy'])->name('address.destroy');
        Route::delete('contact-translation/{contact_translation}', [ContactTranslationController::class, 'destroy'])->name('contact-translation.destroy');
        Route::delete('province-translation/{province_translation}', [ProvinceTranslationController::class, 'destroy'])->name('province-translation.destroy');
        Route::delete('location-translation/{location_translation}', [LocationTranslationController::class, 'destroy'])->name('location-translation.destroy');
        Route::delete('address-translation/{address_translation}', [AddressTranslationController::class, 'destroy'])->name('address-translation.destroy');
        Route::delete('item-translation/{item_translation}', [ItemTranslationController::class, 'destroy'])->name('item-translation.destroy');
        Route::delete('collection-translation/{collection_translation}', [CollectionTranslationController::class, 'destroy'])->name('collection-translation.destroy');
        Route::delete('collection/{collection}', [CollectionController::class, 'destroy'])->name('collection.destroy');
        Route::delete('collection/{collection}/detach-item', [CollectionController::class, 'detachItem'])->name('collection.detachItem');
        Route::delete('collection/{collection}/detach-items', [CollectionController::class, 'detachItems'])->name('collection.detachItems');
        Route::delete('theme/{theme}', [ThemeController::class, 'destroy'])->name('theme.destroy');
        Route::delete('theme-translation/{theme_translation}', [ThemeTranslationController::class, 'destroy'])->name('theme-translation.destroy');
    });
});

// Mobile authentication routes - public (no auth required for login flow)
Route::post('mobile/acquire-token', [MobileAppAuthenticationController::class, 'acquire_token'])
    ->name('token.acquire');

Route::post('mobile/verify-two-factor', [MobileAppAuthenticationController::class, 'verify_two_factor'])
    ->name('token.verify_two_factor');

Route::post('mobile/two-factor-status', [MobileAppAuthenticationController::class, 'two_factor_status'])
    ->name('token.two_factor_status');

Route::get('mobile/wipe', [MobileAppAuthenticationController::class, 'wipe_tokens'])
    ->name('token.wipe')
    ->middleware('auth:sanctum');

// Markdown conversion routes - utility endpoints (no auth required)
// These are stateless utility functions that don't access user data
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
