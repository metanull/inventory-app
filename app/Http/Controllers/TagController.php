<?php

namespace App\Http\Controllers;

use App\Http\Resources\TagResource;
use App\Models\Item;
use App\Models\Tag;
use Illuminate\Http\Request;

class TagController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return TagResource::collection(Tag::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            /** @ignoreParam */
            'id' => 'prohibited',
            'internal_name' => 'required|string|unique:tags,internal_name',
            'backward_compatibility' => 'nullable|string',
            'description' => 'required|string',
        ]);
        $tag = Tag::create($validated);
        $tag->refresh();

        return new TagResource($tag);
    }

    /**
     * Display the specified resource.
     */
    public function show(Tag $tag)
    {
        return new TagResource($tag);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Tag $tag)
    {
        $validated = $request->validate([
            /** @ignoreParam */
            'id' => 'prohibited',
            'internal_name' => 'required|string|unique:tags,internal_name,'.$tag->id,
            'backward_compatibility' => 'nullable|string',
            'description' => 'required|string',
        ]);
        $tag->update($validated);
        $tag->refresh();

        return new TagResource($tag);
    }

    /**
     * Get tags for a specific item.
     */
    public function forItem(Request $request, Item $item)
    {
        $tags = Tag::forItem($item)->get();

        return TagResource::collection($tags);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tag $tag)
    {
        $tag->delete();

        return response()->noContent();
    }
}
