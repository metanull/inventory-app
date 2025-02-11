<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use Illuminate\Http\Request;
use App\Http\Resources\PartnerResource;

class PartnerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Partner::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|uuid',
            'internal_name' => 'required',
            'backward_compatibility' => 'nullable|string',
            'type' => 'required|in:museum,institution,individual',
        ]);
        $partner = Partner::create($validated);
        return new PartnerResource($partner);
    }

    /**
     * Display the specified resource.
     */
    public function show(Partner $partner)
    {
        return new PartnerResource($partner);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Partner $partner)
    {
        $validated = $request->validate([
            'id' => 'prohibited|uuid',
            'internal_name' => 'required',
            'backward_compatibility' => 'nullable|string',
            'type' => 'required|in:museum,institution,individual',
        ]);
        $partner->update($validated);
        return new PartnerResource($partner);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Partner $partner)
    {
        $partner->delete();
        return response()->json(null, 204);
    }
}
