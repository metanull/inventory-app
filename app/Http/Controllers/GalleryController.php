<?php

namespace App\Http\Controllers;

use App\Http\Resources\GalleryResource;
use App\Models\Gallery;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

/**
 * Gallery Controller
 *
 * Handles CRUD operations for Galleries.
 * Provides REST API endpoints for managing galleries containing mixed Items and Details.
 */
class GalleryController extends Controller
{
    /**
     * Display a listing of the galleries.
     */
    public function index(): AnonymousResourceCollection
    {
        $galleries = Gallery::with(['translations', 'partners', 'items', 'details'])->get();

        return GalleryResource::collection($galleries);
    }

    /**
     * Store a newly created gallery in storage.
     */
    public function store(Request $request): GalleryResource
    {
        $request->validate([
            'internal_name' => 'required|string|max:255|unique:galleries,internal_name',
            'backward_compatibility' => 'nullable|string|max:255',
        ]);

        $gallery = Gallery::create($request->only([
            'internal_name',
            'backward_compatibility',
        ]));

        $gallery->load(['translations', 'partners', 'items', 'details']);

        return new GalleryResource($gallery);
    }

    /**
     * Display the specified gallery.
     */
    public function show(Gallery $gallery): GalleryResource
    {
        $gallery->load(['translations', 'partners', 'items', 'details']);

        return new GalleryResource($gallery);
    }

    /**
     * Update the specified gallery in storage.
     */
    public function update(Request $request, Gallery $gallery): GalleryResource
    {
        $request->validate([
            'internal_name' => 'sometimes|required|string|max:255|unique:galleries,internal_name,'.$gallery->id,
            'backward_compatibility' => 'nullable|string|max:255',
        ]);

        $gallery->update($request->only([
            'internal_name',
            'backward_compatibility',
        ]));

        $gallery->load(['translations', 'partners', 'items', 'details']);

        return new GalleryResource($gallery);
    }

    /**
     * Remove the specified gallery from storage.
     */
    public function destroy(Gallery $gallery): Response
    {
        $gallery->delete();

        return response()->noContent();
    }
}
