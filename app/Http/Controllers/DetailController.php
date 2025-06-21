<?php

namespace App\Http\Controllers;

use App\Http\Resources\DetailResource;
use App\Models\Detail;
use Illuminate\Http\Request;

class DetailController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return DetailResource::collection(Detail::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            /** @ignoreParam */
            'id' => 'prohibited',
            'item_id' => 'required|uuid',
            'internal_name' => 'required|string',
            'backward_compatibility' => 'nullable|string',
        ]);
        $detail = Detail::create($validated);
        $detail->refresh();
        $detail->load(['item']);

        return new DetailResource($detail);
    }

    /**
     * Display the specified resource.
     */
    public function show(Detail $detail)
    {
        return new DetailResource($detail);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Detail $detail)
    {
        $validated = $request->validate([
            /** @ignoreParam */
            'id' => 'prohibited',
            'item_id' => 'uuid',
            'internal_name' => 'string',
            'backward_compatibility' => 'nullable|string',
        ]);
        $detail->update($validated);
        $detail->refresh();
        $detail->load(['item']);

        return new DetailResource($detail);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Detail $detail)
    {
        $detail->delete();

        return response()->noContent();
    }
}
