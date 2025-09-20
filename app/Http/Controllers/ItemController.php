<?php

namespace App\Http\Controllers;

use App\Http\Requests\Item\ForTagItemRequest;
use App\Http\Requests\Item\IndexItemRequest;
use App\Http\Requests\Item\ShowItemRequest;
use App\Http\Requests\Item\StoreItemRequest;
use App\Http\Requests\Item\UpdateItemRequest;
use App\Http\Requests\Item\UpdateTagsItemRequest;
use App\Http\Requests\Item\WithAllTagsItemRequest;
use App\Http\Requests\Item\WithAnyTagsItemRequest;
use App\Http\Resources\ItemResource;
use App\Models\Item;
use App\Models\Tag;
use App\Support\Includes\AllowList;
use App\Support\Includes\IncludeParser;
use App\Support\Pagination\PaginationParams;

class ItemController extends Controller
{
    /**
     * Expand includes with necessary nested relationships for optimal resource rendering.
     *
     * @param  array<int,string>  $includes
     * @return array<int,string>
     */
    private function expandIncludes(array $includes): array
    {
        $with = $includes;
        if (in_array('partner', $includes, true)) {
            $with[] = 'partner.country';
        }

        return array_values(array_unique($with));
    }

    /**
     * Display a listing of the resource.
     */
    public function index(IndexItemRequest $request)
    {
        $includes = IncludeParser::fromRequest($request, AllowList::for('item'));
        $with = $this->expandIncludes($includes);
        $pagination = PaginationParams::fromRequest($request);

        $query = Item::query()->with($with);
        $paginator = $query->paginate(
            $pagination['per_page'],
            ['*'],
            'page',
            $pagination['page']
        );

        return ItemResource::collection($paginator);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreItemRequest $request)
    {
        $validated = $request->validated();
        $item = Item::create($validated);
        $item->refresh();

        $includes = IncludeParser::fromRequest($request, AllowList::for('item'));
        // Include related models by default when foreign keys are present, plus any requested includes
        $defaults = [];
        if (! empty($item->partner_id)) {
            $defaults[] = 'partner';
        }
        if (! empty($item->country_id)) {
            $defaults[] = 'country';
        }
        if (! empty($item->project_id)) {
            $defaults[] = 'project';
        }
        $item->load($this->expandIncludes(array_values(array_unique(array_merge($defaults, $includes)))));

        return new ItemResource($item);
    }

    /**
     * Display the specified resource.
     */
    public function show(ShowItemRequest $request, Item $item)
    {
        $includes = IncludeParser::fromRequest($request, AllowList::for('item'));
        $item->load($this->expandIncludes($includes));

        return new ItemResource($item);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateItemRequest $request, Item $item)
    {
        $validated = $request->validated();
        $item->update($validated);
        $item->refresh();

        $includes = IncludeParser::fromRequest($request, AllowList::for('item'));
        $item->load($this->expandIncludes($includes));

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
    public function updateTags(UpdateTagsItemRequest $request, Item $item)
    {
        $validated = $request->validated();

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
        $includes = IncludeParser::fromRequest($request, AllowList::for('item'));
        $item->load($includes);

        return new ItemResource($item);
    }

    /**
     * Get items for a specific tag.
     */
    public function forTag(ForTagItemRequest $request, Tag $tag)
    {
        $includes = IncludeParser::fromRequest($request, AllowList::for('item'));
        $with = $this->expandIncludes($includes);
        $items = Item::forTag($tag)->with($with)->get();

        return ItemResource::collection($items);
    }

    /**
     * Get items that have ALL of the specified tags (AND condition).
     */
    public function withAllTags(WithAllTagsItemRequest $request)
    {
        $validated = $request->validated();

        $includes = IncludeParser::fromRequest($request, AllowList::for('item'));
        $with = $this->expandIncludes($includes);
        $items = Item::withAllTags($validated['tags'])->with($with)->get();

        return ItemResource::collection($items);
    }

    /**
     * Get items that have ANY of the specified tags (OR condition).
     */
    public function withAnyTags(WithAnyTagsItemRequest $request)
    {
        $validated = $request->validated();

        $includes = IncludeParser::fromRequest($request, AllowList::for('item'));
        $with = $this->expandIncludes($includes);
        $items = Item::withAnyTags($validated['tags'])->with($with)->get();

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
