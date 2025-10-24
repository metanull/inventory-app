<?php

namespace App\Http\Controllers\Web;

use App\Enums\Permission;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\StorePartnerImageRequest;
use App\Http\Requests\Web\UpdatePartnerImageRequest;
use App\Models\AvailableImage;
use App\Models\Partner;
use App\Models\PartnerImage;
use Illuminate\Http\RedirectResponse;

class PartnerImageController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:'.Permission::CREATE_DATA->value)->only(['create', 'store']);
        $this->middleware('permission:'.Permission::UPDATE_DATA->value)->only(['edit', 'update', 'moveUp', 'moveDown']);
        $this->middleware('permission:'.Permission::DELETE_DATA->value)->only(['destroy', 'detach']);
    }

    /**
     * Show form to attach available image to partner.
     */
    public function create(Partner $partner)
    {
        $availableImages = AvailableImage::orderBy('path')->get();

        return view('partner-images.create', compact('partner', 'availableImages'));
    }

    /**
     * Attach an available image to a partner.
     */
    public function store(StorePartnerImageRequest $request, Partner $partner): RedirectResponse
    {
        $validated = $request->validated();
        $availableImage = AvailableImage::findOrFail($validated['available_image_id']);

        PartnerImage::attachFromAvailableImage($availableImage, $partner->id);

        return redirect()->route('partners.show', $partner)
            ->with('success', 'Image attached successfully');
    }

    /**
     * Show form to edit partner image.
     */
    public function edit(Partner $partner, PartnerImage $partnerImage)
    {
        // Ensure the image belongs to the partner
        if ($partnerImage->partner_id !== $partner->id) {
            abort(404);
        }

        return view('partner-images.edit', compact('partner', 'partnerImage'));
    }

    /**
     * Update the specified partner image.
     */
    public function update(UpdatePartnerImageRequest $request, Partner $partner, PartnerImage $partnerImage): RedirectResponse
    {
        // Ensure the image belongs to the partner
        if ($partnerImage->partner_id !== $partner->id) {
            abort(404);
        }

        $partnerImage->update($request->validated());

        return redirect()->route('partners.show', $partner)
            ->with('success', 'Image updated successfully');
    }

    /**
     * Move partner image up in display order.
     */
    public function moveUp(Partner $partner, PartnerImage $partnerImage): RedirectResponse
    {
        // Ensure the image belongs to the partner
        if ($partnerImage->partner_id !== $partner->id) {
            abort(404);
        }

        $partnerImage->moveUp();

        return redirect()->route('partners.show', $partner)
            ->with('success', 'Image moved up');
    }

    /**
     * Move partner image down in display order.
     */
    public function moveDown(Partner $partner, PartnerImage $partnerImage): RedirectResponse
    {
        // Ensure the image belongs to the partner
        if ($partnerImage->partner_id !== $partner->id) {
            abort(404);
        }

        $partnerImage->moveDown();

        return redirect()->route('partners.show', $partner)
            ->with('success', 'Image moved down');
    }

    /**
     * Detach a partner image and convert it back to available image.
     */
    public function detach(Partner $partner, PartnerImage $partnerImage): RedirectResponse
    {
        // Ensure the image belongs to the partner
        if ($partnerImage->partner_id !== $partner->id) {
            abort(404);
        }

        $partnerImage->detachToAvailableImage();

        return redirect()->route('partners.show', $partner)
            ->with('success', 'Image detached and returned to available images');
    }

    /**
     * Remove the specified partner image permanently.
     */
    public function destroy(Partner $partner, PartnerImage $partnerImage): RedirectResponse
    {
        // Ensure the image belongs to the partner
        if ($partnerImage->partner_id !== $partner->id) {
            abort(404);
        }

        $partnerImage->delete();

        return redirect()->route('partners.show', $partner)
            ->with('success', 'Image deleted permanently');
    }

    /**
     * Returns the file to the caller.
     */
    public function download(Partner $partner, PartnerImage $partnerImage)
    {
        // Ensure the image belongs to the partner
        if ($partnerImage->partner_id !== $partner->id) {
            abort(404);
        }

        $disk = config('localstorage.pictures.disk');
        $filename = $partnerImage->original_name ?: basename($partnerImage->path);

        return \App\Http\Responses\FileResponse::download(
            $disk,
            $partnerImage->path,
            $filename,
            $partnerImage->mime_type
        );
    }

    /**
     * Returns the image file for direct viewing (e.g., for use in <img> src attribute).
     */
    public function view(Partner $partner, PartnerImage $partnerImage)
    {
        // Ensure the image belongs to the partner
        if ($partnerImage->partner_id !== $partner->id) {
            abort(404);
        }

        $disk = config('localstorage.pictures.disk');

        return \App\Http\Responses\FileResponse::view(
            $disk,
            $partnerImage->path,
            $partnerImage->mime_type
        );
    }
}
