<?php

namespace App\Http\Controllers;

use App\Http\Resources\ItemResource;
use App\Models\Item;
use App\Models\Tag;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return ItemResource::collection(Item::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            /** @ignoreParam */
            'id' => 'prohibited',
            'partner_id' => 'nullable|uuid',
            'internal_name' => 'required|string',
            'backward_compatibility' => 'nullable|string',
            'type' => 'required|in:object,monument',
            'country_id' => 'nullable|string|size:3',
            'project_id' => 'nullable|uuid',
        ]);
        $item = Item::create($validated);
        $item->refresh();
        $item->load(['partner', 'country', 'project', 'tags']);

        return new ItemResource($item);
    }

    /**
     * Display the specified resource.
     */
    public function show(Item $item)
    {
        return new ItemResource($item);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Item $item)
    {
        $validated = $request->validate([
            /** @ignoreParam */
            'id' => 'prohibited',
            'partner_id' => 'nullable|uuid',
            'internal_name' => 'required|string',
            'backward_compatibility' => 'nullable|string',
            'type' => 'required|in:object,monument',
            'country_id' => 'nullable|string|size:3',
            'project_id' => 'nullable|uuid',
        ]);
        $item->update($validated);
        $item->refresh();
        $item->load(['partner', 'country', 'project', 'tags']);

        return new ItemResource($item);
    }

    /**
     * Get items for a specific tag.
     */
    public function forTag(Request $request, Tag $tag)
    {
        $items = Item::forTag($tag)->with(['partner', 'country', 'project', 'tags'])->get();

        return ItemResource::collection($items);
    }

    /**
     * Get items that have ALL of the specified tags (AND condition).
     */
    public function withAllTags(Request $request)
    {
        $validated = $request->validate([
            'tags' => 'required|array|min:1',
            'tags.*' => 'required|uuid|exists:tags,id',
        ]);

        $items = Item::withAllTags($validated['tags'])
            ->with(['partner', 'country', 'project', 'tags'])
            ->get();

        return ItemResource::collection($items);
    }

    /**
     * Get items that have ANY of the specified tags (OR condition).
     */
    public function withAnyTags(Request $request)
    {
        $validated = $request->validate([
            'tags' => 'required|array|min:1',
            'tags.*' => 'required|uuid|exists:tags,id',
        ]);

        $items = Item::withAnyTags($validated['tags'])
            ->with(['partner', 'country', 'project', 'tags'])
            ->get();

        return ItemResource::collection($items);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Item $item)
    {
        $item->delete();

        return response()->noContent();
    }
}
