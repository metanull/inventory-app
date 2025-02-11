<?php

namespace App\Http\Controllers;

use App\Models\Context;
use Illuminate\Http\Request;
use App\Http\Resources\ContextResource;

class ContextController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Context::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|uuid',
            'internal_name' => 'required',
            'backward_compatibility' => 'nullable|string'
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
            'id' => 'prohibited|uuid',
            'internal_name' => 'required',
            'backward_compatibility' => 'nullable|string'
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
