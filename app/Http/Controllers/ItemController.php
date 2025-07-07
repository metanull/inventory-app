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
     * Update tags for the specified item without modifying other item properties.
     *
     * This endpoint allows quick editing of tag associations by specifying which tags
     * to attach or detach from the item. It provides fine-grained control over tag
     * operations without requiring a full item update.
     *
     * @param  Request  $request  - Contains 'attach' and/or 'detach' arrays of tag UUIDs
     * @param  Item  $item  - The item to update tags for
     * @return ItemResource - Updated item with current tag associations
     */
    public function updateTags(Request $request, Item $item)
    {
        $validated = $request->validate([
            'attach' => 'sometimes|array',
            'attach.*' => 'required|uuid|exists:tags,id',
            'detach' => 'sometimes|array',
            'detach.*' => 'required|uuid|exists:tags,id',
        ]);

        // Attach new tags (if any)
        if (isset($validated['attach'])) {
            // Only attach tags that aren't already attached to avoid duplicates
            $existingTagIds = $item->tags()->pluck('tags.id')->toArray();
            $tagsToAttach = array_diff($validated['attach'], $existingTagIds);

            if (! empty($tagsToAttach)) {
                $item->tags()->attach($tagsToAttach);
            }
        }

        // Detach specified tags (if any)
        if (isset($validated['detach'])) {
            $item->tags()->detach($validated['detach']);
        }

        // Refresh and load relationships to return updated data
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
