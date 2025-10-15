<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\IndexTagForItemRequest;
use App\Http\Requests\Api\IndexTagRequest;
use App\Http\Requests\Api\ShowTagRequest;
use App\Http\Requests\Api\StoreTagRequest;
use App\Http\Requests\Api\UpdateTagRequest;
use App\Http\Resources\TagResource;
use App\Models\Item;
use App\Models\Tag;

class TagController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexTagRequest $request)
    {
        $pagination = $request->getPaginationParams();
        $paginator = Tag::query()->paginate(
            $pagination['per_page'],
            ['*'],
            'page',
            $pagination['page']
        );

        return TagResource::collection($paginator);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return TagResource
     */
    public function store(StoreTagRequest $request)
    {
        $validated = $request->validated();
        $tag = Tag::create($validated);
        $tag->refresh();

        return new TagResource($tag);
    }

    /**
     * Display the specified resource.
     */
    public function show(ShowTagRequest $request, Tag $tag)
    {
        return new TagResource($tag);
    }

    /**
     * Update the specified resource in storage.
     *
     * @return TagResource
     */
    public function update(UpdateTagRequest $request, Tag $tag)
    {
        $validated = $request->validated();
        $tag->update($validated);
        $tag->refresh();

        return new TagResource($tag);
    }

    /**
     * Get tags for a specific item.
     */
    public function forItem(IndexTagForItemRequest $request, Item $item)
    {
        $pagination = $request->getPaginationParams();
        $paginator = Tag::forItem($item)->paginate(
            $pagination['per_page'],
            ['*'],
            'page',
            $pagination['page']
        );

        return TagResource::collection($paginator);
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
