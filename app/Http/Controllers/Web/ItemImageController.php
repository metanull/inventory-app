<?php

namespace App\Http\Controllers\Web;

use App\Enums\Permission;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\StoreItemImageRequest;
use App\Http\Requests\Web\UpdateItemImageRequest;
use App\Models\AvailableImage;
use App\Models\Item;
use App\Models\ItemImage;
use Illuminate\Http\RedirectResponse;

class ItemImageController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:'.Permission::CREATE_DATA->value)->only(['create', 'store']);
        $this->middleware('permission:'.Permission::UPDATE_DATA->value)->only(['edit', 'update', 'move_up', 'move_down']);
        $this->middleware('permission:'.Permission::DELETE_DATA->value)->only(['destroy', 'detach']);
    }

    /**
     * Show form to attach available image to item.
     */
    public function create(Item $item)
    {
        $availableImages = AvailableImage::orderBy('path')->get();

        return view('item-images.create', compact('item', 'availableImages'));
    }

    /**
     * Attach an available image to an item.
     */
    public function store(StoreItemImageRequest $request, Item $item): RedirectResponse
    {
        $validated = $request->validated();
        $availableImage = AvailableImage::findOrFail($validated['available_image_id']);

        ItemImage::attachFromAvailableImage($availableImage, $item->id);

        return redirect()->route('items.show', $item)
            ->with('success', 'Image attached successfully');
    }

    /**
     * Show form to edit item image.
     */
    public function edit(Item $item, ItemImage $itemImage)
    {
        // Ensure the image belongs to the item
        if ($itemImage->item_id !== $item->id) {
            abort(404);
        }

        return view('item-images.edit', compact('item', 'itemImage'));
    }

    /**
     * Update the specified item image.
     */
    public function update(UpdateItemImageRequest $request, Item $item, ItemImage $itemImage): RedirectResponse
    {
        // Ensure the image belongs to the item
        if ($itemImage->item_id !== $item->id) {
            abort(404);
        }

        $itemImage->update($request->validated());

        return redirect()->route('items.show', $item)
            ->with('success', 'Image updated successfully');
    }

    /**
     * Move item image up in display order.
     */
    public function moveUp(Item $item, ItemImage $itemImage): RedirectResponse
    {
        // Ensure the image belongs to the item
        if ($itemImage->item_id !== $item->id) {
            abort(404);
        }

        $itemImage->moveUp();

        return redirect()->route('items.show', $item)
            ->with('success', 'Image moved up');
    }

    /**
     * Move item image down in display order.
     */
    public function moveDown(Item $item, ItemImage $itemImage): RedirectResponse
    {
        // Ensure the image belongs to the item
        if ($itemImage->item_id !== $item->id) {
            abort(404);
        }

        $itemImage->moveDown();

        return redirect()->route('items.show', $item)
            ->with('success', 'Image moved down');
    }

    /**
     * Detach an item image and convert it back to available image.
     */
    public function detach(Item $item, ItemImage $itemImage): RedirectResponse
    {
        // Ensure the image belongs to the item
        if ($itemImage->item_id !== $item->id) {
            abort(404);
        }

        $itemImage->detachToAvailableImage();

        return redirect()->route('items.show', $item)
            ->with('success', 'Image detached and returned to available images');
    }

    /**
     * Remove the specified item image permanently.
     */
    public function destroy(Item $item, ItemImage $itemImage): RedirectResponse
    {
        // Ensure the image belongs to the item
        if ($itemImage->item_id !== $item->id) {
            abort(404);
        }

        $itemImage->delete();

        return redirect()->route('items.show', $item)
            ->with('success', 'Image deleted permanently');
    }

    /**
     * Returns the file to the caller.
     */
    public function download(Item $item, ItemImage $itemImage)
    {
        // Ensure the image belongs to the item
        if ($itemImage->item_id !== $item->id) {
            abort(404);
        }

        $disk = config('localstorage.pictures.disk');
        $directory = trim(config('localstorage.pictures.directory'), '/');
        $filename = $itemImage->original_name ?: basename($itemImage->path);

        // Prepend directory to path
        $storagePath = $directory.'/'.$itemImage->path;

        return \App\Http\Responses\FileResponse::download(
            $disk,
            $storagePath,
            $filename,
            $itemImage->mime_type
        );
    }

    /**
     * Returns the image file for direct viewing (e.g., for use in <img> src attribute).
     */
    public function view(Item $item, ItemImage $itemImage)
    {
        // Ensure the image belongs to the item
        if ($itemImage->item_id !== $item->id) {
            abort(404);
        }

        $disk = config('localstorage.pictures.disk');
        $directory = trim(config('localstorage.pictures.directory'), '/');

        // Prepend directory to path
        $storagePath = $directory.'/'.$itemImage->path;

        return \App\Http\Responses\FileResponse::view(
            $disk,
            $storagePath,
            $itemImage->mime_type
        );
    }
}
