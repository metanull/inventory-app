<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\IndexAvailableImageRequest;
use App\Http\Requests\Api\UpdateAvailableImageRequest;
use App\Http\Resources\AvailableImageResource;
use App\Http\Responses\Image\DownloadImageResponse;
use App\Http\Responses\Image\InlineImageResponse;
use App\Models\AvailableImage;
use Illuminate\Support\Facades\Storage;

class AvailableImageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexAvailableImageRequest $request): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        $includes = $request->getIncludeParams();
        $pagination = $request->getPaginationParams();

        $query = AvailableImage::query();
        if (! empty($includes)) {
            $query->with($includes);
        }

        $paginator = $query->paginate($pagination['per_page'], ['*'], 'page', $pagination['page']);

        return AvailableImageResource::collection($paginator);
    }

    /**
     * Update the specified resource in storage.
     *
     * @return AvailableImageResource
     */
    public function update(UpdateAvailableImageRequest $request, AvailableImage $availableImage): AvailableImageResource
    {
        $validated = $request->validated();
        $availableImage->update($validated);
        $availableImage->refresh();

        return new AvailableImageResource($availableImage);
    }

    /**
     * Display the specified resource.
     */
    public function show(AvailableImage $availableImage): AvailableImageResource
    {
        return new AvailableImageResource($availableImage);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AvailableImage $availableImage): \Illuminate\Http\Response
    {
        Storage::delete($availableImage->path);
        $availableImage->delete();

        return response()->noContent();
    }

    /**
     * Download the file to the caller.
     */
    public function download(AvailableImage $availableImage): \Illuminate\Contracts\Support\Responsable
    {
        return new DownloadImageResponse($availableImage);
    }

    /**
     * Returns the image file for direct viewing (e.g., for use in <img> src attribute).
     */
    public function view(AvailableImage $availableImage): \Illuminate\Contracts\Support\Responsable
    {
        return new InlineImageResponse($availableImage);
    }
}
