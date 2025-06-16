<?php

namespace App\Http\Controllers;

use App\Http\Resources\PartnerResource;
use App\Models\Partner;
use Illuminate\Http\Request;

class PartnerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return PartnerResource::collection(Partner::all());
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
            'type' => 'required|in:museum,institution,individual',
            'country_id' => 'nullable|string|size:3',
        ]);
        $partner = Partner::create($validated);
        $partner->refresh();
        $partner->load('country');

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
            /** @ignoreParam */
            'id' => 'prohibited',
            'internal_name' => 'string',
            'backward_compatibility' => 'nullable|string',
            'type' => 'in:museum,institution,individual',
            'country_id' => 'nullable|string|size:3',
        ]);
        $partner->update($validated);
        $partner->refresh();
        $partner->load('country');

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
