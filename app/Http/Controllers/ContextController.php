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
            /** @ignoreParam */
            'id' => 'prohibited',
            'internal_name' => 'required|string',
            'backward_compatibility' => 'nullable|string',
            'is_default' => 'prohibited|boolean',
        ]);
        $context = Context::create($validated);
        $context->refresh();

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
            /** @ignoreParam */
            'id' => 'prohibited',
            'internal_name' => 'required|string',
            'backward_compatibility' => 'nullable|string',
            'is_default' => 'prohibited|boolean',
        ]);
        $context->update($validated);
        $context->refresh();

        return new ContextResource($context);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Context $context)
    {
        $context->delete();

        return response()->noContent();
    }

    /**
     * Set a context as the default one.
     */
    public function setDefault(Request $request, Context $context)
    {
        $validated = $request->validate([
            'is_default' => 'required|boolean',
        ]);

        if (true === $validated['is_default']) {
            $context->setDefault();
        }
        $context->refresh();

        return new ContextResource($context);
    }

    /**
     * Get the default context.
     */
    public function getDefault()
    {
        $context = Context::default()->first();
        if (! $context) {
            return response()->json(['message' => 'No default context found'], 404);
        }

        return new ContextResource($context);
    }
}
