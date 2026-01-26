<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\IndexItemItemLinkTranslationRequest;
use App\Http\Requests\Api\ShowItemItemLinkTranslationRequest;
use App\Http\Requests\Api\StoreItemItemLinkTranslationRequest;
use App\Http\Requests\Api\UpdateItemItemLinkTranslationRequest;
use App\Http\Resources\ItemItemLinkTranslationResource;
use App\Models\ItemItemLinkTranslation;

/**
 * @tags Item Item Link Translations
 */
class ItemItemLinkTranslationController extends Controller
{
    /**
     * Display a listing of item-item link translations
     */
    public function index(IndexItemItemLinkTranslationRequest $request)
    {
        $query = ItemItemLinkTranslation::query();

        // Allow filtering by item_item_link_id or language_id
        if ($request->has('item_item_link_id')) {
            $query->where('item_item_link_id', $request->item_item_link_id);
        }

        if ($request->has('language_id')) {
            $query->where('language_id', $request->language_id);
        }

        $pagination = $request->getPaginationParams();
        $translations = $query->with(['itemItemLink', 'language'])
            ->paginate($pagination['per_page'], ['*'], 'page', $pagination['page']);

        return ItemItemLinkTranslationResource::collection($translations);
    }

    /**
     * Store a newly created item-item link translation
     *
     * @return ItemItemLinkTranslationResource
     */
    public function store(StoreItemItemLinkTranslationRequest $request)
    {
        $data = $request->validated();
        $translation = ItemItemLinkTranslation::create($data);
        $translation->refresh();
        $translation->load(['itemItemLink', 'language']);

        return new ItemItemLinkTranslationResource($translation);
    }

    /**
     * Display the specified item-item link translation
     */
    public function show(ShowItemItemLinkTranslationRequest $request, ItemItemLinkTranslation $itemItemLinkTranslation)
    {
        $includes = $request->getIncludeParams();
        if (! empty($includes)) {
            $itemItemLinkTranslation->load($includes);
        }

        return new ItemItemLinkTranslationResource($itemItemLinkTranslation);
    }

    /**
     * Update the specified item-item link translation
     *
     * @return ItemItemLinkTranslationResource
     */
    public function update(UpdateItemItemLinkTranslationRequest $request, ItemItemLinkTranslation $itemItemLinkTranslation)
    {
        $data = $request->validated();
        $itemItemLinkTranslation->update($data);
        $itemItemLinkTranslation->refresh();
        $itemItemLinkTranslation->load(['itemItemLink', 'language']);

        return new ItemItemLinkTranslationResource($itemItemLinkTranslation);
    }

    /**
     * Remove the specified item-item link translation
     */
    public function destroy(ItemItemLinkTranslation $itemItemLinkTranslation)
    {
        $itemItemLinkTranslation->delete();

        return response()->noContent();
    }
}
