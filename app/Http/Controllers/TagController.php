<?php

namespace App\Http\Controllers;

use App\Http\Requests\Tag\ForItemTagRequest;
use App\Http\Requests\Tag\IndexTagRequest;
use App\Http\Requests\Tag\ShowTagRequest;
use App\Http\Requests\Tag\StoreTagRequest;
use App\Http\Requests\Tag\UpdateTagRequest;
use App\Http\Resources\TagResource;
use App\Models\Item;
use App\Models\Tag;
use App\Support\Pagination\PaginationParams;

class TagController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexTagRequest $request)
    {
        $pagination = PaginationParams::fromRequest($request);
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
    public function forItem(ForItemTagRequest $request, Item $item)
    {
        $pagination = PaginationParams::fromRequest($request);
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
