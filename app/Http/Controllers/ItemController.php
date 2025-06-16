<?php

namespace App\Http\Controllers;

use App\Http\Resources\ItemResource;
use App\Models\Item;
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
            'partner_id' => 'required|uuid',
            'internal_name' => 'required|string',
            'backward_compatibility' => 'nullable|string',
            'type' => 'required|in:object,monument',
            'country_id' => 'nullable|string|size:3',
            'project_id' => 'nullable|uuid',
        ]);
        $item = Item::create($validated);
        $item->refresh();
        $item->load(['partner', 'country', 'project']);

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
            'partner_id' => 'uuid',
            'internal_name' => 'string',
            'backward_compatibility' => 'nullable|string',
            'type' => 'in:object,monument',
            'country_id' => 'nullable|string|size:3',
            'project_id' => 'nullable|uuid',
        ]);
        $item->update($validated);
        $item->refresh();
        $item->load(['partner', 'country', 'project']);

        return new ItemResource($item);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Item $item)
    {
        $item->delete();

        return response()->json(null, 204);
    }
}
