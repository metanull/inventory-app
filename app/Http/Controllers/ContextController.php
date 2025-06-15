<?php

namespace App\Http\Controllers;

use App\Http\Resources\ContextResource;
use App\Models\Context;
use Illuminate\Http\Request;

class ContextController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return ContextResource::collection(Context::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'prohibited',
            'internal_name' => 'required',
            'backward_compatibility' => 'nullable|string',
            'is_default' => 'prohibited|boolean',
        ]);
        $context = Context::create($validated);

        return new ContextResource($context);
    }

    /**
     * Display the specified resource.
     */
    public function show(Context $context)
    {
        return new ContextResource($context);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Context $context)
    {
        $validated = $request->validate([
            'id' => 'prohibited',
            'internal_name' => 'required',
            'backward_compatibility' => 'nullable|string',
            'is_default' => 'prohibited|boolean',
        ]);
        $context->update($validated);

        return new ContextResource($context);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Context $context)
    {
        $context->delete();

        return response()->json(null, 204);
    }
}
