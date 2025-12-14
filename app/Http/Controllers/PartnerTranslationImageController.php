<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\AttachFromAvailablePartnerTranslationImageRequest;
use App\Http\Requests\Api\IndexPartnerTranslationImageRequest;
use App\Http\Requests\Api\ShowPartnerTranslationImageRequest;
use App\Http\Requests\Api\StorePartnerTranslationImageRequest;
use App\Http\Requests\Api\UpdatePartnerTranslationImageRequest;
use App\Http\Resources\PartnerTranslationImageResource;
use App\Models\AvailableImage;
use App\Models\PartnerTranslation;
use App\Models\PartnerTranslationImage;
use App\Support\Includes\AllowList;
use App\Support\Includes\IncludeParser;

class PartnerTranslationImageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexPartnerTranslationImageRequest $request)
    {
        $includes = $request->getIncludeParams();
        $pagination = $request->getPaginationParams();

        $query = PartnerTranslationImage::query()->with($includes);
        $paginator = $query->paginate(
            $pagination['per_page'],
            ['*'],
            'page',
            $pagination['page']
        );

        return PartnerTranslationImageResource::collection($paginator);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return PartnerTranslationImageResource
     */
    public function store(StorePartnerTranslationImageRequest $request)
    {
        $validated = $request->validated();
        $partnerTranslationImage = PartnerTranslationImage::create($validated);
        $partnerTranslationImage->refresh();
        $includes = IncludeParser::fromRequest($request, AllowList::for('partner_translation_image'));
        $partnerTranslationImage->load($includes);

        return new PartnerTranslationImageResource($partnerTranslationImage);
    }

    /**
     * Display the specified resource.
     */
    public function show(ShowPartnerTranslationImageRequest $request, PartnerTranslationImage $partnerTranslationImage)
    {
        $includes = $request->getIncludeParams();
        $partnerTranslationImage->load($includes);

        return new PartnerTranslationImageResource($partnerTranslationImage);
    }

    /**
     * Update the specified resource in storage.
     *
     * @return PartnerTranslationImageResource
     */
    public function update(UpdatePartnerTranslationImageRequest $request, PartnerTranslationImage $partnerTranslationImage)
    {
        $validated = $request->validated();
        $partnerTranslationImage->update($validated);
        $partnerTranslationImage->refresh();
        $includes = IncludeParser::fromRequest($request, AllowList::for('partner_translation_image'));
        $partnerTranslationImage->load($includes);

        return new PartnerTranslationImageResource($partnerTranslationImage);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PartnerTranslationImage $partnerTranslationImage)
    {
        $partnerTranslationImage->delete();

        return response()->noContent();
    }

    /**
     * Move partner translation image up in display order.
     */
    public function moveUp(PartnerTranslationImage $partnerTranslationImage)
    {
        $partnerTranslationImage->moveUp();

        // Refresh the model to get updated data
        $partnerTranslationImage->refresh();

        return new PartnerTranslationImageResource($partnerTranslationImage);
    }

    /**
     * Move partner translation image down in display order.
     */
    public function moveDown(PartnerTranslationImage $partnerTranslationImage)
    {
        $partnerTranslationImage->moveDown();

        // Refresh the model to get updated data
        $partnerTranslationImage->refresh();

        return new PartnerTranslationImageResource($partnerTranslationImage);
    }

    /**
     * Tighten ordering for all images of the partner translation.
     */
    public function tightenOrdering(PartnerTranslationImage $partnerTranslationImage)
    {
        $partnerTranslationImage->tightenOrderingForPartnerTranslation();

        return new \App\Http\Resources\OperationSuccessResource([
            'success' => true,
            'message' => 'Image ordering tightened successfully',
        ]);
    }

    /**
     * Attach an available image to a partner translation.
     *
     * @return PartnerTranslationImageResource
     */
    public function attachFromAvailable(AttachFromAvailablePartnerTranslationImageRequest $request, PartnerTranslation $partnerTranslation)
    {
        $validated = $request->validated();

        $availableImage = AvailableImage::findOrFail($validated['available_image_id']);
        $partnerTranslationImage = PartnerTranslationImage::attachFromAvailableImage($availableImage, $partnerTranslation->id, $validated['alt_text'] ?? null);

        $includes = $request->getIncludeParams();
        if (! empty($includes)) {
            $partnerTranslationImage->load($includes);
        }

        return new PartnerTranslationImageResource($partnerTranslationImage);
    }

    /**
     * Detach a partner translation image and convert it back to available image.
     */
    public function detachToAvailable(PartnerTranslationImage $partnerTranslationImage)
    {
        $availableImage = $partnerTranslationImage->detachToAvailableImage();

        return new \App\Http\Resources\OperationSuccessResource([
            'success' => true,
            'message' => 'Image detached successfully',
            'available_image_id' => $availableImage->id,
        ]);
    }

    /**
     * Returns the file to the caller.
     */
    public function download(PartnerTranslationImage $partnerTranslationImage)
    {
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
    public function view(PartnerTranslationImage $partnerTranslationImage)
    {
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
