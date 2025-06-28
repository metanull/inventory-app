<?php

namespace App\Http\Controllers;

use App\Http\Resources\TagItemResource;
use App\Models\TagItem;
use Illuminate\Http\Request;

class TagItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return TagItemResource::collection(TagItem::with(['tag', 'item'])->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            /** @ignoreParam */
            'id' => 'prohibited',
            'tag_id' => 'required|uuid|exists:tags,id',
            'item_id' => 'required|uuid|exists:items,id',
        ]);

        $tagItem = TagItem::create($validated);
        $tagItem->load(['tag', 'item']);
        $tagItem->refresh();

        return new TagItemResource($tagItem);
    }

    /**
     * Display the specified resource.
     */
    public function show(TagItem $tagItem)
    {
        $tagItem->load(['tag', 'item']);

        return new TagItemResource($tagItem);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TagItem $tagItem)
    {
        $validated = $request->validate([
            /** @ignoreParam */
            'id' => 'prohibited',
            'tag_id' => 'required|uuid|exists:tags,id',
            'item_id' => 'required|uuid|exists:items,id',
        ]);

        $tagItem->update($validated);
        $tagItem->load(['tag', 'item']);
        $tagItem->refresh();

        return new TagItemResource($tagItem);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TagItem $tagItem)
    {
        $tagItem->delete();

        return response()->noContent();
    }
}
