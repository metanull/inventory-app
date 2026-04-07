<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\StoreItemMediaRequest;
use App\Http\Requests\Api\UpdateItemMediaRequest;
use App\Http\Resources\ItemMediaResource;
use App\Models\Item;
use App\Models\ItemMedia;

class ItemMediaController extends Controller
{
    /**
     * Display a listing of item media for a specific item.
     */
    public function index(Item $item)
    {
        $itemMedia = $item->itemMedia()->orderBy('type')->orderBy('display_order')->get();

        return ItemMediaResource::collection($itemMedia);
    }

    /**
     * Store a newly created item media.
     */
    public function store(StoreItemMediaRequest $request, Item $item)
    {
        $validated = $request->validated();
        $validated['item_id'] = $item->id;
        $validated['display_order'] ??= ItemMedia::getNextDisplayOrderFor([
            'item_id' => $item->id,
            'type' => $validated['type'],
        ]);

        $itemMedia = ItemMedia::create($validated);

        return new ItemMediaResource($itemMedia);
    }

    /**
     * Display the specified item media.
     */
    public function show(ItemMedia $itemMedia)
    {
        return new ItemMediaResource($itemMedia);
    }

    /**
     * Update the specified item media.
     */
    public function update(UpdateItemMediaRequest $request, ItemMedia $itemMedia)
    {
        $validated = $request->validated();
        $itemMedia->update($validated);
        $itemMedia->refresh();

        return new ItemMediaResource($itemMedia);
    }

    /**
     * Move item media up in display order.
     */
    public function moveUp(ItemMedia $itemMedia)
    {
        $itemMedia->moveUp();
        $itemMedia->refresh();

        return new ItemMediaResource($itemMedia);
    }

    /**
     * Move item media down in display order.
     */
    public function moveDown(ItemMedia $itemMedia)
    {
        $itemMedia->moveDown();
        $itemMedia->refresh();

        return new ItemMediaResource($itemMedia);
    }

    /**
     * Remove the specified item media.
     */
    public function destroy(ItemMedia $itemMedia)
    {
        $itemMedia->delete();

        return response()->noContent();
    }
}
