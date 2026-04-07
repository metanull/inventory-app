<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\AttachFromAvailableContributorImageRequest;
use App\Http\Requests\Api\IndexContributorImageRequest;
use App\Http\Requests\Api\ShowContributorImageRequest;
use App\Http\Requests\Api\StoreContributorImageRequest;
use App\Http\Requests\Api\UpdateContributorImageRequest;
use App\Http\Resources\ContributorImageResource;
use App\Http\Resources\OperationSuccessResource;
use App\Http\Responses\FileResponse;
use App\Models\AvailableImage;
use App\Models\Contributor;
use App\Models\ContributorImage;
use App\Support\Includes\AllowList;
use App\Support\Includes\IncludeParser;

class ContributorImageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexContributorImageRequest $request)
    {
        $includes = $request->getIncludeParams();
        $pagination = $request->getPaginationParams();

        $query = ContributorImage::query()->with($includes);
        $paginator = $query->paginate(
            $pagination['per_page'],
            ['*'],
            'page',
            $pagination['page']
        );

        return ContributorImageResource::collection($paginator);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return ContributorImageResource
     */
    public function store(StoreContributorImageRequest $request)
    {
        $validated = $request->validated();
        $contributorImage = ContributorImage::create($validated);
        $contributorImage->refresh();
        $includes = IncludeParser::fromRequest($request, AllowList::for('contributor_image'));
        $contributorImage->load($includes);

        return new ContributorImageResource($contributorImage);
    }

    /**
     * Display the specified resource.
     */
    public function show(ShowContributorImageRequest $request, ContributorImage $contributorImage)
    {
        $includes = $request->getIncludeParams();
        $contributorImage->load($includes);

        return new ContributorImageResource($contributorImage);
    }

    /**
     * Update the specified resource in storage.
     *
     * @return ContributorImageResource
     */
    public function update(UpdateContributorImageRequest $request, ContributorImage $contributorImage)
    {
        $validated = $request->validated();
        $contributorImage->update($validated);
        $contributorImage->refresh();
        $includes = IncludeParser::fromRequest($request, AllowList::for('contributor_image'));
        $contributorImage->load($includes);

        return new ContributorImageResource($contributorImage);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ContributorImage $contributorImage)
    {
        $contributorImage->delete();

        return response()->noContent();
    }

    /**
     * Move contributor image up in display order.
     */
    public function moveUp(ContributorImage $contributorImage)
    {
        $contributorImage->moveUp();
        $contributorImage->refresh();

        return new ContributorImageResource($contributorImage);
    }

    /**
     * Move contributor image down in display order.
     */
    public function moveDown(ContributorImage $contributorImage)
    {
        $contributorImage->moveDown();
        $contributorImage->refresh();

        return new ContributorImageResource($contributorImage);
    }

    /**
     * Tighten ordering for all images of the contributor.
     */
    public function tightenOrdering(ContributorImage $contributorImage)
    {
        $contributorImage->tightenOrdering();

        return new OperationSuccessResource([
            'success' => true,
            'message' => 'Image ordering tightened successfully',
        ]);
    }

    /**
     * Attach an available image to a contributor.
     *
     * @return ContributorImageResource
     */
    public function attachFromAvailable(AttachFromAvailableContributorImageRequest $request, Contributor $contributor)
    {
        $validated = $request->validated();

        $availableImage = AvailableImage::findOrFail($validated['available_image_id']);
        $contributorImage = ContributorImage::attachFromAvailableImage($availableImage, $contributor->id, $validated['alt_text'] ?? null);

        $includes = $request->getIncludeParams();
        if (! empty($includes)) {
            $contributorImage->load($includes);
        }

        return new ContributorImageResource($contributorImage);
    }

    /**
     * Detach a contributor image and convert it back to available image.
     */
    public function detachToAvailable(ContributorImage $contributorImage)
    {
        $availableImage = $contributorImage->detachToAvailableImage();

        return new OperationSuccessResource([
            'success' => true,
            'message' => 'Image detached successfully',
            'available_image_id' => $availableImage->id,
        ]);
    }

    /**
     * Returns the file to the caller.
     */
    public function download(ContributorImage $contributorImage)
    {
        $disk = config('localstorage.pictures.disk');
        $directory = trim(config('localstorage.pictures.directory'), '/');
        $filename = $contributorImage->original_name ?: basename($contributorImage->path);

        $storagePath = $directory.'/'.$contributorImage->path;

        return FileResponse::download(
            $disk,
            $storagePath,
            $filename,
            $contributorImage->mime_type
        );
    }

    /**
     * Returns the image file for direct viewing.
     */
    public function view(ContributorImage $contributorImage)
    {
        $disk = config('localstorage.pictures.disk');
        $directory = trim(config('localstorage.pictures.directory'), '/');

        $storagePath = $directory.'/'.$contributorImage->path;

        return FileResponse::view(
            $disk,
            $storagePath,
            $contributorImage->mime_type
        );
    }
}
