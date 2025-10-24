<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\AttachFromAvailablePartnerImageRequest;
use App\Http\Requests\Api\IndexPartnerImageRequest;
use App\Http\Requests\Api\ShowPartnerImageRequest;
use App\Http\Requests\Api\StorePartnerImageRequest;
use App\Http\Requests\Api\UpdatePartnerImageRequest;
use App\Http\Resources\PartnerImageResource;
use App\Models\AvailableImage;
use App\Models\Partner;
use App\Models\PartnerImage;
use App\Support\Includes\AllowList;
use App\Support\Includes\IncludeParser;

class PartnerImageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexPartnerImageRequest $request)
    {
        $includes = $request->getIncludeParams();
        $pagination = $request->getPaginationParams();

        $query = PartnerImage::query()->with($includes);
        $paginator = $query->paginate(
            $pagination['per_page'],
            ['*'],
            'page',
            $pagination['page']
        );

        return PartnerImageResource::collection($paginator);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return PartnerImageResource
     */
    public function store(StorePartnerImageRequest $request)
    {
        $validated = $request->validated();
        $partnerImage = PartnerImage::create($validated);
        $partnerImage->refresh();
        $includes = IncludeParser::fromRequest($request, AllowList::for('partner_image'));
        $partnerImage->load($includes);

        return new PartnerImageResource($partnerImage);
    }

    /**
     * Display the specified resource.
     */
    public function show(ShowPartnerImageRequest $request, PartnerImage $partnerImage)
    {
        $includes = $request->getIncludeParams();
        $partnerImage->load($includes);

        return new PartnerImageResource($partnerImage);
    }

    /**
     * Update the specified resource in storage.
     *
     * @return PartnerImageResource
     */
    public function update(UpdatePartnerImageRequest $request, PartnerImage $partnerImage)
    {
        $validated = $request->validated();
        $partnerImage->update($validated);
        $partnerImage->refresh();
        $includes = IncludeParser::fromRequest($request, AllowList::for('partner_image'));
        $partnerImage->load($includes);

        return new PartnerImageResource($partnerImage);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PartnerImage $partnerImage)
    {
        $partnerImage->delete();

        return response()->noContent();
    }

    /**
     * Move partner image up in display order.
     */
    public function moveUp(PartnerImage $partnerImage)
    {
        $partnerImage->moveUp();

        // Refresh the model to get updated data
        $partnerImage->refresh();

        return new PartnerImageResource($partnerImage);
    }

    /**
     * Move partner image down in display order.
     */
    public function moveDown(PartnerImage $partnerImage)
    {
        $partnerImage->moveDown();

        // Refresh the model to get updated data
        $partnerImage->refresh();

        return new PartnerImageResource($partnerImage);
    }

    /**
     * Tighten ordering for all images of the partner.
     */
    public function tightenOrdering(PartnerImage $partnerImage)
    {
        $partnerImage->tightenOrderingForPartner();

        return new \App\Http\Resources\OperationSuccessResource([
            'success' => true,
            'message' => 'Image ordering tightened successfully',
        ]);
    }

    /**
     * Attach an available image to a partner.
     *
     * @return PartnerImageResource
     */
    public function attachFromAvailable(AttachFromAvailablePartnerImageRequest $request, Partner $partner)
    {
        $validated = $request->validated();

        $availableImage = AvailableImage::findOrFail($validated['available_image_id']);
        $partnerImage = PartnerImage::attachFromAvailableImage($availableImage, $partner->id, $validated['alt_text'] ?? null);

        $includes = $request->getIncludeParams();
        if (! empty($includes)) {
            $partnerImage->load($includes);
        }

        return new PartnerImageResource($partnerImage);
    }

    /**
     * Detach a partner image and convert it back to available image.
     */
    public function detachToAvailable(PartnerImage $partnerImage)
    {
        $availableImage = $partnerImage->detachToAvailableImage();

        return new \App\Http\Resources\OperationSuccessResource([
            'success' => true,
            'message' => 'Image detached successfully',
            'available_image_id' => $availableImage->id,
        ]);
    }

    /**
     * Returns the file to the caller.
     */
    public function download(PartnerImage $partnerImage)
    {
        // PartnerImages share files with AvailableImages (same disk/path), so use the available images disk
        $disk = config('localstorage.available.images.disk');
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
    public function view(PartnerImage $partnerImage)
    {
        // PartnerImages share files with AvailableImages (same disk/path), so use the available images disk
        $disk = config('localstorage.available.images.disk');

        return \App\Http\Responses\FileResponse::view(
            $disk,
            $partnerImage->path,
            $partnerImage->mime_type
        );
    }
}
