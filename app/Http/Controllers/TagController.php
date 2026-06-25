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
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class TagController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexTagRequest $request): AnonymousResourceCollection
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
     */
    public function store(StoreTagRequest $request): TagResource
    {
        $validated = $request->validated();
        $tag = Tag::create($validated);
        $tag->refresh();

        return new TagResource($tag);
    }

    /**
     * Display the specified resource.
     */
    public function show(ShowTagRequest $request, Tag $tag): TagResource
    {
        return new TagResource($tag);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTagRequest $request, Tag $tag): TagResource
    {
        $validated = $request->validated();
        $tag->update($validated);
        $tag->refresh();

        return new TagResource($tag);
    }

    /**
     * Get tags for a specific item.
     */
    public function forItem(IndexTagForItemRequest $request, Item $item): AnonymousResourceCollection
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
    public function destroy(Tag $tag): Response
    {
        $tag->delete();

        return response()->noContent();
    }
}
