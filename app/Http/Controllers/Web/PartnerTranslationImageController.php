<?php

namespace App\Http\Controllers\Web;

use App\Enums\Permission;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\StorePartnerTranslationImageRequest;
use App\Http\Requests\Web\UpdatePartnerTranslationImageRequest;
use App\Models\AvailableImage;
use App\Models\PartnerTranslation;
use App\Models\PartnerTranslationImage;
use Illuminate\Http\RedirectResponse;

class PartnerTranslationImageController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:'.Permission::CREATE_DATA->value)->only(['create', 'store']);
        $this->middleware('permission:'.Permission::UPDATE_DATA->value)->only(['edit', 'update', 'moveUp', 'moveDown']);
        $this->middleware('permission:'.Permission::DELETE_DATA->value)->only(['destroy', 'detach']);
    }

    /**
     * Show form to attach available image to partner translation.
     */
    public function create(PartnerTranslation $partnerTranslation)
    {
        $availableImages = AvailableImage::orderBy('path')->get();

        return view('partner-translation-images.create', compact('partnerTranslation', 'availableImages'));
    }

    /**
     * Attach an available image to a partner translation.
     */
    public function store(StorePartnerTranslationImageRequest $request, PartnerTranslation $partnerTranslation): RedirectResponse
    {
        $validated = $request->validated();
        $availableImage = AvailableImage::findOrFail($validated['available_image_id']);

        PartnerTranslationImage::attachFromAvailableImage($availableImage, $partnerTranslation->id);

        return redirect()->route('partner-translations.show', $partnerTranslation)
            ->with('success', 'Image attached successfully');
    }

    /**
     * Show form to edit partner translation image.
     */
    public function edit(PartnerTranslation $partnerTranslation, PartnerTranslationImage $partnerTranslationImage)
    {
        // Ensure the image belongs to the partner translation
        if ($partnerTranslationImage->partner_translation_id !== $partnerTranslation->id) {
            abort(404);
        }

        return view('partner-translation-images.edit', compact('partnerTranslation', 'partnerTranslationImage'));
    }

    /**
     * Update the specified partner translation image.
     */
    public function update(UpdatePartnerTranslationImageRequest $request, PartnerTranslation $partnerTranslation, PartnerTranslationImage $partnerTranslationImage): RedirectResponse
    {
        // Ensure the image belongs to the partner translation
        if ($partnerTranslationImage->partner_translation_id !== $partnerTranslation->id) {
            abort(404);
        }

        $partnerTranslationImage->update($request->validated());

        return redirect()->route('partner-translations.show', $partnerTranslation)
            ->with('success', 'Image updated successfully');
    }

    /**
     * Move partner translation image up in display order.
     */
    public function moveUp(PartnerTranslation $partnerTranslation, PartnerTranslationImage $partnerTranslationImage): RedirectResponse
    {
        // Ensure the image belongs to the partner translation
        if ($partnerTranslationImage->partner_translation_id !== $partnerTranslation->id) {
            abort(404);
        }

        $partnerTranslationImage->moveUp();

        return redirect()->route('partner-translations.show', $partnerTranslation)
            ->with('success', 'Image moved up');
    }

    /**
     * Move partner translation image down in display order.
     */
    public function moveDown(PartnerTranslation $partnerTranslation, PartnerTranslationImage $partnerTranslationImage): RedirectResponse
    {
        // Ensure the image belongs to the partner translation
        if ($partnerTranslationImage->partner_translation_id !== $partnerTranslation->id) {
            abort(404);
        }

        $partnerTranslationImage->moveDown();

        return redirect()->route('partner-translations.show', $partnerTranslation)
            ->with('success', 'Image moved down');
    }

    /**
     * Detach a partner translation image and convert it back to available image.
     */
    public function detach(PartnerTranslation $partnerTranslation, PartnerTranslationImage $partnerTranslationImage): RedirectResponse
    {
        // Ensure the image belongs to the partner translation
        if ($partnerTranslationImage->partner_translation_id !== $partnerTranslation->id) {
            abort(404);
        }

        $partnerTranslationImage->detachToAvailableImage();

        return redirect()->route('partner-translations.show', $partnerTranslation)
            ->with('success', 'Image detached and returned to available images');
    }

    /**
     * Remove the specified partner translation image permanently.
     */
    public function destroy(PartnerTranslation $partnerTranslation, PartnerTranslationImage $partnerTranslationImage): RedirectResponse
    {
        // Ensure the image belongs to the partner translation
        if ($partnerTranslationImage->partner_translation_id !== $partnerTranslation->id) {
            abort(404);
        }

        $partnerTranslationImage->delete();

        return redirect()->route('partner-translations.show', $partnerTranslation)
            ->with('success', 'Image deleted permanently');
    }

    /**
     * Returns the file to the caller.
     */
    public function download(PartnerTranslation $partnerTranslation, PartnerTranslationImage $partnerTranslationImage)
    {
        // Ensure the image belongs to the partner translation
        if ($partnerTranslationImage->partner_translation_id !== $partnerTranslation->id) {
            abort(404);
        }

        $disk = config('localstorage.pictures.disk');
        $directory = trim(config('localstorage.pictures.directory'), '/');
        $filename = $partnerTranslationImage->original_name ?: basename($partnerTranslationImage->path);

        // Prepend directory to path
        $storagePath = $directory.'/'.$partnerTranslationImage->path;

        return \App\Http\Responses\FileResponse::download(
            $disk,
            $storagePath,
            $filename,
            $partnerTranslationImage->mime_type
        );
    }

    /**
     * Returns the image file for direct viewing (e.g., for use in <img> src attribute).
     */
    public function view(PartnerTranslation $partnerTranslation, PartnerTranslationImage $partnerTranslationImage)
    {
        // Ensure the image belongs to the partner translation
        if ($partnerTranslationImage->partner_translation_id !== $partnerTranslation->id) {
            abort(404);
        }

        $disk = config('localstorage.pictures.disk');
        $directory = trim(config('localstorage.pictures.directory'), '/');

        // Prepend directory to path
        $storagePath = $directory.'/'.$partnerTranslationImage->path;

        return \App\Http\Responses\FileResponse::view(
            $disk,
            $storagePath,
            $partnerTranslationImage->mime_type
        );
    }
}
