<?php

namespace App\Http\Controllers;

use App\Models\ContextItem;
use Illuminate\Http\Request;
use App\Http\Resources\ContextItemResource;

class ContextItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return ContextItem::all();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|uuid',
            'context_id' => 'required|uuid|exists:contexts,id',
            'item_id' => 'required|uuid|exists:items,id',
            'language_id' => 'required|string|size:3|exists:languages,id',
            'name' => 'required|string',
            'description' => 'nullable|string',
            'metadata' => 'nullable|json',
        ]);
        $context = ContextItem::create($validated);
        return new ContextItemResource($context);
    }

    /**
     * Display the specified resource.
     */
    public function show(ContextItem $context)
    {
        return new ContextItemResource($context);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ContextItem $context)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ContextItem $context)
    {
        $validated = $request->validate([
            'id' => 'prohibited|uuid',
            'context_id' => 'required|uuid|exists:contexts,id',
            'item_id' => 'required|uuid|exists:items,id',
            'language_id' => 'required|uuid|exists:languages,id',
            'name' => 'required|string',
            'description' => 'nullable|string',
            'metadata' => 'nullable|json',
        ]);
        $context->update($validated);
        return new ContextItemResource($context);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ContextItem $context)
    {
        $context->delete();
        return response()->json(null, 204);
    }
}
