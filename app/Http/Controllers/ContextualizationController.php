<?php

namespace App\Http\Controllers;

use App\Http\Resources\ContextualizationResource;
use App\Models\Context;
use App\Models\Contextualization;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class ContextualizationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): AnonymousResourceCollection
    {
        $contextualizations = Contextualization::with(['context', 'item', 'detail'])->paginate();

        return ContextualizationResource::collection($contextualizations);
    }

    /**
     * Display a listing of contextualizations from the default context.
     */
    public function defaultContext(): AnonymousResourceCollection
    {
        $contextualizations = Contextualization::with(['context', 'item', 'detail'])
            ->default()
            ->paginate();

        return ContextualizationResource::collection($contextualizations);
    }

    /**
     * Display a listing of contextualizations for items only.
     */
    public function forItems(): AnonymousResourceCollection
    {
        $contextualizations = Contextualization::with(['context', 'item'])
            ->forItems()
            ->paginate();

        return ContextualizationResource::collection($contextualizations);
    }

    /**
     * Display a listing of contextualizations for details only.
     */
    public function forDetails(): AnonymousResourceCollection
    {
        $contextualizations = Contextualization::with(['context', 'detail'])
            ->forDetails()
            ->paginate();

        return ContextualizationResource::collection($contextualizations);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'context_id' => 'required|uuid|exists:contexts,id',
            'item_id' => 'nullable|uuid|exists:items,id',
            'detail_id' => 'nullable|uuid|exists:details,id',
            'extra' => 'nullable|array',
            'internal_name' => 'required|string|unique:contextualizations,internal_name',
            'backward_compatibility' => 'nullable|string',
        ]);

        // Ensure exactly one of item_id or detail_id is set
        if (($validated['item_id'] && $validated['detail_id']) || (! $validated['item_id'] && ! $validated['detail_id'])) {
            return response()->json([
                'message' => 'Exactly one of item_id or detail_id must be provided.',
                'errors' => [
                    'item_id' => ['Exactly one of item_id or detail_id must be provided.'],
                    'detail_id' => ['Exactly one of item_id or detail_id must be provided.'],
                ],
            ], 422);
        }

        $contextualization = Contextualization::create($validated);
        $contextualization->load(['context', 'item', 'detail']);

        return new ContextualizationResource($contextualization);
    }

    /**
     * Store a contextualization using the default context.
     */
    public function storeWithDefaultContext(Request $request)
    {
        $validated = $request->validate([
            'item_id' => 'nullable|uuid|exists:items,id',
            'detail_id' => 'nullable|uuid|exists:details,id',
            'extra' => 'nullable|array',
            'internal_name' => 'required|string|unique:contextualizations,internal_name',
            'backward_compatibility' => 'nullable|string',
        ]);

        // Ensure exactly one of item_id or detail_id is set
        if (($validated['item_id'] && $validated['detail_id']) || (! $validated['item_id'] && ! $validated['detail_id'])) {
            return response()->json([
                'message' => 'Exactly one of item_id or detail_id must be provided.',
                'errors' => [
                    'item_id' => ['Exactly one of item_id or detail_id must be provided.'],
                    'detail_id' => ['Exactly one of item_id or detail_id must be provided.'],
                ],
            ], 422);
        }

        $contextualization = Contextualization::createWithDefaultContext($validated);
        $contextualization->load(['context', 'item', 'detail']);

        return new ContextualizationResource($contextualization);
    }

    /**
     * Display the specified resource.
     */
    public function show(Contextualization $contextualization): ContextualizationResource
    {
        $contextualization->load(['context', 'item', 'detail']);

        return new ContextualizationResource($contextualization);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Contextualization $contextualization)
    {
        $validated = $request->validate([
            'context_id' => 'sometimes|uuid|exists:contexts,id',
            'item_id' => 'nullable|uuid|exists:items,id',
            'detail_id' => 'nullable|uuid|exists:details,id',
            'extra' => 'nullable|array',
            'internal_name' => [
                'sometimes',
                'string',
                Rule::unique('contextualizations', 'internal_name')->ignore($contextualization->id),
            ],
            'backward_compatibility' => 'nullable|string',
        ]);

        // If both item_id and detail_id are provided in the update, validate the constraint
        if (array_key_exists('item_id', $validated) || array_key_exists('detail_id', $validated)) {
            $itemId = array_key_exists('item_id', $validated) ? $validated['item_id'] : $contextualization->item_id;
            $detailId = array_key_exists('detail_id', $validated) ? $validated['detail_id'] : $contextualization->detail_id;

            if (($itemId && $detailId) || (! $itemId && ! $detailId)) {
                return response()->json([
                    'message' => 'Exactly one of item_id or detail_id must be provided.',
                    'errors' => [
                        'item_id' => ['Exactly one of item_id or detail_id must be provided.'],
                        'detail_id' => ['Exactly one of item_id or detail_id must be provided.'],
                    ],
                ], 422);
            }
        }

        $contextualization->update($validated);
        $contextualization->load(['context', 'item', 'detail']);

        return new ContextualizationResource($contextualization);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Contextualization $contextualization): \Illuminate\Http\JsonResponse
    {
        $contextualization->delete();

        return response()->json(null, 204);
    }
}
