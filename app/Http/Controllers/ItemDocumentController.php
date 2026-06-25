<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\StoreItemDocumentRequest;
use App\Http\Requests\Api\UpdateItemDocumentRequest;
use App\Http\Resources\ItemDocumentResource;
use App\Http\Responses\FileResponse;
use App\Models\Item;
use App\Models\ItemDocument;
use Illuminate\Support\Facades\Config;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ItemDocumentController extends Controller
{
    /**
     * Display a listing of item documents for a specific item.
     */
    public function index(Item $item): AnonymousResourceCollection
    {
        $itemDocuments = $item->itemDocuments()->orderBy('display_order')->get();

        return ItemDocumentResource::collection($itemDocuments);
    }

    /**
     * Store a newly created item document.
     */
    public function store(StoreItemDocumentRequest $request, Item $item): ItemDocumentResource
    {
        $validated = $request->validated();
        $validated['item_id'] = $item->id;
        $validated['display_order'] ??= ItemDocument::getNextDisplayOrderFor([
            'item_id' => $item->id,
        ]);

        $itemDocument = ItemDocument::create($validated);

        return new ItemDocumentResource($itemDocument);
    }

    /**
     * Display the specified item document.
     */
    public function show(ItemDocument $itemDocument): ItemDocumentResource
    {
        return new ItemDocumentResource($itemDocument);
    }

    /**
     * Update the specified item document.
     */
    public function update(UpdateItemDocumentRequest $request, ItemDocument $itemDocument): ItemDocumentResource
    {
        $validated = $request->validated();
        $itemDocument->update($validated);
        $itemDocument->refresh();

        return new ItemDocumentResource($itemDocument);
    }

    /**
     * Move item document up in display order.
     */
    public function moveUp(ItemDocument $itemDocument): ItemDocumentResource
    {
        $itemDocument->moveUp();
        $itemDocument->refresh();

        return new ItemDocumentResource($itemDocument);
    }

    /**
     * Move item document down in display order.
     */
    public function moveDown(ItemDocument $itemDocument): ItemDocumentResource
    {
        $itemDocument->moveDown();
        $itemDocument->refresh();

        return new ItemDocumentResource($itemDocument);
    }

    /**
     * Remove the specified item document.
     */
    public function destroy(ItemDocument $itemDocument): Response
    {
        $itemDocument->delete();

        return response()->noContent();
    }

    /**
     * Returns the document file for download.
     */
    public function download(ItemDocument $itemDocument): Responsable
    {
        $disk = Config::string('localstorage.documents.disk');
        $directory = trim(Config::string('localstorage.documents.directory'), '/');
        $filename = $itemDocument->original_name ?: basename($itemDocument->path);

        $storagePath = $directory.'/'.$itemDocument->path;

        return FileResponse::download(
            $disk,
            $storagePath,
            $filename,
            $itemDocument->mime_type
        );
    }
}
