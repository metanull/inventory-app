<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\AttachTagItemRequest;
use App\Http\Requests\Api\AttachTagsItemRequest;
use App\Http\Requests\Api\ByTypeItemRequest;
use App\Http\Requests\Api\ChildrenItemRequest;
use App\Http\Requests\Api\DetachTagItemRequest;
use App\Http\Requests\Api\DetachTagsItemRequest;
use App\Http\Requests\Api\ForTagItemRequest;
use App\Http\Requests\Api\IndexItemRequest;
use App\Http\Requests\Api\ParentsItemRequest;
use App\Http\Requests\Api\ShowItemRequest;
use App\Http\Requests\Api\StoreItemRequest;
use App\Http\Requests\Api\UpdateItemRequest;
use App\Http\Requests\Api\UpdateTagsItemRequest;
use App\Http\Requests\Api\WithAllTagsItemRequest;
use App\Http\Requests\Api\WithAnyTagsItemRequest;
use App\Http\Resources\ItemResource;
use App\Models\Item;
use App\Models\Tag;
use App\Support\Includes\AllowList;
use App\Support\Includes\IncludeParser;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

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
        if (in_array('parent', $includes, true)) {
            $with[] = 'parent';
        }
        if (in_array('children', $includes, true)) {
            $with[] = 'children';
        }
        if (in_array('itemImages', $includes, true)) {
            $with[] = 'itemImages';
        }

        return array_values(array_unique($with));
    }

    /**
     * Display a listing of the resource.
     */
    public function index(IndexItemRequest $request): AnonymousResourceCollection
    {
        $includes = $request->getIncludeParams();
        $with = $this->expandIncludes($includes);
        $pagination = $request->getPaginationParams();

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
    public function store(StoreItemRequest $request): ItemResource
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
    public function show(ShowItemRequest $request, Item $item): ItemResource
    {
        $includes = $request->getIncludeParams();
        $item->load($this->expandIncludes($includes));

        return new ItemResource($item);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateItemRequest $request, Item $item): ItemResource
    {
        $validated = $request->validated();
        $item->update($validated);
        $item->refresh();

        $includes = IncludeParser::fromRequest($request, AllowList::for('item'));
        $item->load($this->expandIncludes($includes));

        return new ItemResource($item);
    }

    /**
     * Update the tags associated with an item.
     * This endpoint handles attaching and/or detaching tags from an item using a single operation.
     * Designed for granular tag management, allowing callers to perform specific tag attach/detach
     * operations without requiring a full item update.
     *
     * @param  UpdateTagsItemRequest  $request  - Contains 'attach' and/or 'detach' arrays of tag UUIDs
     * @param  Item  $item  - The item to update tags for
     * @return ItemResource - Updated item with current tag associations
     */
    public function updateTags(UpdateTagsItemRequest $request, Item $item): ItemResource
    {
        $validated = $request->validated();

        // Attach new tags (if any)
        if (isset($validated['attach'])) {
            // Only attach tags that aren't already attached to avoid duplicates
            $existingTagIds = $item->tags()->pluck('tags.id')->toArray();
            /** @var array<int, mixed> $attachIds */
            $attachIds = is_array($validated['attach']) ? $validated['attach'] : [];
            $tagsToAttach = array_diff($attachIds, $existingTagIds);

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
     * Attach a single tag to an item.
     */
    public function attachTag(AttachTagItemRequest $request, Item $item): ItemResource
    {
        $validated = $request->validated();

        // Check if tag is already attached
        if (! $item->tags()->where('tags.id', $validated['tag_id'])->exists()) {
            $item->tags()->attach($validated['tag_id']);
        }

        // Refresh and load relationships
        $item->refresh();
        $includes = $request->getIncludeParams();
        $item->load($this->expandIncludes($includes));

        return new ItemResource($item);
    }

    /**
     * Detach a single tag from an item.
     */
    public function detachTag(DetachTagItemRequest $request, Item $item): ItemResource
    {
        $validated = $request->validated();

        $item->tags()->detach($validated['tag_id']);

        // Refresh and load relationships
        $item->refresh();
        $includes = $request->getIncludeParams();
        $item->load($this->expandIncludes($includes));

        return new ItemResource($item);
    }

    /**
     * Attach multiple tags to an item.
     */
    public function attachTags(AttachTagsItemRequest $request, Item $item): ItemResource
    {
        $validated = $request->validated();

        // Only attach tags that aren't already attached
        $existingTagIds = $item->tags()->pluck('tags.id')->toArray();
        /** @var array<int, mixed> $tagIds */
        $tagIds = is_array($validated['tag_ids'] ?? null) ? $validated['tag_ids'] : [];
        $tagsToAttach = array_diff($tagIds, $existingTagIds);

        if (! empty($tagsToAttach)) {
            $item->tags()->attach($tagsToAttach);
        }

        // Refresh and load relationships
        $item->refresh();
        $includes = $request->getIncludeParams();
        $item->load($this->expandIncludes($includes));

        return new ItemResource($item);
    }

    /**
     * Detach multiple tags from an item.
     */
    public function detachTags(DetachTagsItemRequest $request, Item $item): ItemResource
    {
        $validated = $request->validated();

        $item->tags()->detach($validated['tag_ids']);

        // Refresh and load relationships
        $item->refresh();
        $includes = $request->getIncludeParams();
        $item->load($this->expandIncludes($includes));

        return new ItemResource($item);
    }

    /**
     * Get items for a specific tag.
     */
    public function forTag(ForTagItemRequest $request, Tag $tag): AnonymousResourceCollection
    {
        $includes = $request->getIncludeParams();
        $with = $this->expandIncludes($includes);
        $items = Item::forTag($tag)->with($with)->get();

        return ItemResource::collection($items);
    }

    /**
     * Get items that have ALL of the specified tags (AND condition).
     */
    public function withAllTags(WithAllTagsItemRequest $request): AnonymousResourceCollection
    {
        $validated = $request->validated();

        $includes = $request->getIncludeParams();
        $with = $this->expandIncludes($includes);
        /** @var array<int, mixed> $tags */
        $tags = is_array($validated['tags'] ?? null) ? $validated['tags'] : [];
        $items = Item::withAllTags($tags)->with($with)->get();

        return ItemResource::collection($items);
    }

    /**
     * Get items that have ANY of the specified tags (OR condition).
     */
    public function withAnyTags(WithAnyTagsItemRequest $request): AnonymousResourceCollection
    {
        $validated = $request->validated();

        $includes = $request->getIncludeParams();
        $with = $this->expandIncludes($includes);
        /** @var array<int, mixed> $tags */
        $tags = is_array($validated['tags'] ?? null) ? $validated['tags'] : [];
        $items = Item::withAnyTags($tags)->with($with)->get();

        return ItemResource::collection($items);
    }

    /**
     * Get items by type.
     */
    public function byType(ByTypeItemRequest $request, string $type): AnonymousResourceCollection
    {
        $request->validated();

        $includes = $request->getIncludeParams();
        $with = $this->expandIncludes($includes);

        $query = Item::query()->with($with);

        switch ($type) {
            case 'object':
                $query->objects();
                break;
            case 'monument':
                $query->monuments();
                break;
            case 'detail':
                $query->details();
                break;
            case 'picture':
                $query->pictures();
                break;
        }

        $items = $query->get();

        return ItemResource::collection($items);
    }

    /**
     * Get parent items (items with no parent).
     */
    public function parents(ParentsItemRequest $request): AnonymousResourceCollection
    {
        $includes = $request->getIncludeParams();
        $with = $this->expandIncludes($includes);
        $items = Item::parents()->with($with)->get();

        return ItemResource::collection($items);
    }

    /**
     * Get child items (items with a parent).
     */
    public function children(ChildrenItemRequest $request): AnonymousResourceCollection
    {
        $includes = $request->getIncludeParams();
        $with = $this->expandIncludes($includes);
        $items = Item::query()->children()->with($with)->get();

        return ItemResource::collection($items);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Item $item): Response
    {
        $item->delete();

        return response()->noContent();
    }
}
