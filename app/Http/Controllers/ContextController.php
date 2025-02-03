<?php

namespace App\Http\Controllers;

use App\Models\Context;
use Illuminate\Http\Request;

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
        /*request()->validate([
            'id' => 'required|uuid|unique:contexts',
            'internal_name' => 'nullable|string',
        ]);

        $context = Context::create($request->all());
        return response()->json($context, 201);
        */

        $context = new Context();
        $context->id = (string) \Illuminate\Support\Str::uuid();
        $context->internal_name = $request->input('internal_name');
        $context->backward_compatibility = $request->input('backward_compatibility');
        $context->save();
        return response()->json($context, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Context $context)
    {
        return $context;
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Context $context)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Context $context)
    {
        request()->validate([
            'internal_name' => 'required|string',
            'backward_compatibility' => 'nullable|string',
        ]);

        $context->update($request->all());
        return response()->json($context, 200);
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
