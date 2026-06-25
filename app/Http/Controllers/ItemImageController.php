<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\AttachFromAvailableItemImageRequest;
use App\Http\Requests\Api\IndexItemImageRequest;
use App\Http\Requests\Api\ShowItemImageRequest;
use App\Http\Requests\Api\StoreItemImageRequest;
use App\Http\Requests\Api\UpdateItemImageRequest;
use App\Http\Resources\ItemImageResource;
use App\Http\Resources\OperationSuccessResource;
use App\Http\Responses\Image\DownloadImageResponse;
use App\Http\Responses\Image\InlineImageResponse;
use App\Models\AvailableImage;
use App\Models\Item;
use App\Models\ItemImage;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ItemImageController extends Controller
{
    /**
     * Display a listing of item images for a specific item.
     */
    public function index(IndexItemImageRequest $request, Item $item): AnonymousResourceCollection
    {
        $includes = $request->getIncludeParams();
        $itemImages = $item->itemImages()->orderBy('display_order');

        if (! empty($includes)) {
            $itemImages->with($includes);
        }

        return ItemImageResource::collection($itemImages->get());
    }

    /**
     * Store a newly created item image.
     */
    public function store(StoreItemImageRequest $request, Item $item): ItemImageResource
    {
        $validated = $request->validated();
        $validated['item_id'] = $item->id;
        $validated['display_order'] = ItemImage::getNextDisplayOrderForItem($item->id);

        $itemImage = ItemImage::create($validated);

        return new ItemImageResource($itemImage);
    }

    /**
     * Display the specified item image.
     */
    public function show(ShowItemImageRequest $request, ItemImage $itemImage): ItemImageResource
    {
        $includes = $request->getIncludeParams();

        if (! empty($includes)) {
            $itemImage->load($includes);
        }

        return new ItemImageResource($itemImage);
    }

    /**
     * Update the specified item image.
     */
    public function update(UpdateItemImageRequest $request, ItemImage $itemImage): ItemImageResource
    {
        $validated = $request->validated();
        $itemImage->update($validated);

        $includes = $request->getIncludeParams();
        if (! empty($includes)) {
            $itemImage->load($includes);
        }

        return new ItemImageResource($itemImage);
    }

    /**
     * Move item image up in display order.
     */
    public function moveUp(ItemImage $itemImage): ItemImageResource
    {
        $itemImage->moveUp();

        // Refresh the model to get updated data
        $itemImage->refresh();

        return new ItemImageResource($itemImage);
    }

    /**
     * Move item image down in display order.
     */
    public function moveDown(ItemImage $itemImage): ItemImageResource
    {
        $itemImage->moveDown();

        // Refresh the model to get updated data
        $itemImage->refresh();

        return new ItemImageResource($itemImage);
    }

    /**
     * Tighten ordering for all images of the item.
     */
    public function tightenOrdering(ItemImage $itemImage): OperationSuccessResource
    {
        $itemImage->tightenOrderingForItem();

        return new OperationSuccessResource([
            'success' => true,
            'message' => 'Image ordering tightened successfully',
        ]);
    }

    /**
     * Attach an available image to an item.
     */
    public function attachFromAvailable(AttachFromAvailableItemImageRequest $request, Item $item): ItemImageResource
    {
        $validated = $request->validated();

        $availableImage = AvailableImage::findOrFail((string) $validated['available_image_id']);
        $itemImage = ItemImage::attachFromAvailableImage($availableImage, $item->id, $validated['alt_text'] ?? null);

        $includes = $request->getIncludeParams();
        if (! empty($includes)) {
            $itemImage->load($includes);
        }

        return new ItemImageResource($itemImage);
    }

    /**
     * Detach an item image and convert it back to available image.
     */
    public function detachToAvailable(ItemImage $itemImage): OperationSuccessResource
    {
        $availableImage = $itemImage->detachToAvailableImage();

        return new OperationSuccessResource([
            'success' => true,
            'message' => 'Image detached successfully',
            'available_image_id' => $availableImage->id,
        ]);
    }

    /**
     * Remove the specified item image.
     */
    public function destroy(ItemImage $itemImage): Response
    {
        $itemImage->delete();

        return response()->noContent();
    }

    /**
     * Returns the file to the caller.
     */
    public function download(ItemImage $itemImage): Responsable
    {
        return new DownloadImageResponse($itemImage);
    }

    /**
     * Returns the image file for direct viewing (e.g., for use in <img> src attribute).
     */
    public function view(ItemImage $itemImage): Responsable
    {
        return new InlineImageResponse($itemImage);
    }
}
