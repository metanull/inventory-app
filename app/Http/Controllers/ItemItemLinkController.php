<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\IndexItemItemLinkRequest;
use App\Http\Requests\Api\StoreItemItemLinkRequest;
use App\Http\Requests\Api\UpdateItemItemLinkRequest;
use App\Http\Resources\ItemItemLinkResource;
use App\Models\ItemItemLink;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ItemItemLinkController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexItemItemLinkRequest $request): AnonymousResourceCollection
    {
        $query = ItemItemLink::query();

        // Apply filters from request if provided
        if ($request->has('source_id')) {
            $query->fromSource($request->input('source_id'));
        }

        if ($request->has('target_id')) {
            $query->toTarget($request->input('target_id'));
        }

        if ($request->has('context_id')) {
            $query->inContext($request->input('context_id'));
        }

        if ($request->has('item_id')) {
            $query->involvingItem($request->input('item_id'));
        }

        $pagination = $request->getPaginationParams();
        $paginator = $query->paginate(
            $pagination['per_page'],
            ['*'],
            'page',
            $pagination['page']
        );

        return ItemItemLinkResource::collection($paginator);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreItemItemLinkRequest $request): ItemItemLinkResource
    {
        $validated = $request->validated();
        $link = ItemItemLink::create($validated);
        $link->refresh();

        return new ItemItemLinkResource($link);
    }

    /**
     * Display the specified resource.
     */
    public function show(ItemItemLink $link): ItemItemLinkResource
    {
        return new ItemItemLinkResource($link);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateItemItemLinkRequest $request, ItemItemLink $link): ItemItemLinkResource
    {
        $validated = $request->validated();
        $link->update($validated);
        $link->refresh();

        return new ItemItemLinkResource($link);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ItemItemLink $link): Response
    {
        $link->delete();

        return response()->noContent();
    }
}
