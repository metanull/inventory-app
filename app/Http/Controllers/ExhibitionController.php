<?php

namespace App\Http\Controllers;

use App\Http\Resources\ExhibitionResource;
use App\Models\Exhibition;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;

class ExhibitionController extends Controller
{
    /**
     * Display a listing of the exhibitions.
     */
    public function index(Request $request)
    {
        $exhibitions = Exhibition::with(['translations', 'partners'])->paginate(15);

        return ExhibitionResource::collection($exhibitions);
    }

    /**
     * Store a newly created exhibition in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'internal_name' => ['required', 'string', 'unique:exhibitions,internal_name'],
            'backward_compatibility' => ['nullable', 'string'],
        ]);
        $exhibition = Exhibition::create($validated);

        return new ExhibitionResource($exhibition->load(['translations', 'partners']));
    }

    /**
     * Display the specified exhibition.
     */
    public function show(Exhibition $exhibition)
    {
        $exhibition->load(['translations', 'partners']);

        return new ExhibitionResource($exhibition);
    }

    /**
     * Update the specified exhibition in storage.
     */
    public function update(Request $request, Exhibition $exhibition)
    {
        $validated = $request->validate([
            'internal_name' => ['sometimes', 'string', Rule::unique('exhibitions', 'internal_name')->ignore($exhibition->id, 'id')],
            'backward_compatibility' => ['nullable', 'string'],
        ]);
        $exhibition->update($validated);

        return new ExhibitionResource($exhibition->load(['translations', 'partners']));
    }

    /**
     * Remove the specified exhibition from storage.
     */
    public function destroy(Exhibition $exhibition)
    {
        $exhibition->delete();

        return response()->noContent();
    }
}
