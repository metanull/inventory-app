<?php

namespace App\Http\Controllers;

use App\Http\Requests\Gallery\DestroyGalleryRequest;
use App\Http\Requests\Gallery\IndexGalleryRequest;
use App\Http\Requests\Gallery\ShowGalleryRequest;
use App\Http\Requests\Gallery\StoreGalleryRequest;
use App\Http\Requests\Gallery\UpdateGalleryRequest;
use App\Http\Resources\GalleryResource;
use App\Models\Gallery;
use App\Support\Includes\AllowList;
use App\Support\Includes\IncludeParser;
use App\Support\Pagination\PaginationParams;
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
    public function index(IndexGalleryRequest $request): AnonymousResourceCollection
    {
        $includes = IncludeParser::fromRequest($request, AllowList::for('gallery'));
        $pagination = PaginationParams::fromRequest($request);

        $defaults = ['translations', 'partners', 'items', 'details'];
        $with = array_values(array_unique(array_merge($defaults, $includes)));

        $query = Gallery::query()->with($with);

        $paginator = $query->paginate(
            $pagination['per_page'],
            ['*'],
            'page',
            $pagination['page']
        );

        return GalleryResource::collection($paginator);
    }

    /**
     * Store a newly created gallery in storage.
     */
    public function store(StoreGalleryRequest $request): GalleryResource
    {
        $validated = $request->validated();

        $gallery = Gallery::create([
            'internal_name' => $validated['internal_name'],
            'backward_compatibility' => $validated['backward_compatibility'] ?? null,
        ]);

        $requested = IncludeParser::fromRequest($request, AllowList::for('gallery'));
        $defaults = ['translations', 'partners', 'items', 'details'];
        $gallery->load(array_values(array_unique(array_merge($defaults, $requested))));

        return new GalleryResource($gallery);
    }

    /**
     * Display the specified gallery.
     */
    public function show(ShowGalleryRequest $request, Gallery $gallery): GalleryResource
    {
        $includes = IncludeParser::fromRequest($request, AllowList::for('gallery'));
        if (! empty($includes)) {
            $gallery->load($includes);
        }

        return new GalleryResource($gallery);
    }

    /**
     * Update the specified gallery in storage.
     */
    public function update(UpdateGalleryRequest $request, Gallery $gallery): GalleryResource
    {
        $validated = $request->validated();

        $gallery->update([
            'internal_name' => $validated['internal_name'] ?? $gallery->internal_name,
            'backward_compatibility' => $validated['backward_compatibility'] ?? $gallery->backward_compatibility,
        ]);

        $requested = IncludeParser::fromRequest($request, AllowList::for('gallery'));
        $defaults = ['translations', 'partners', 'items', 'details'];
        $gallery->load(array_values(array_unique(array_merge($defaults, $requested))));

        return new GalleryResource($gallery);
    }

    /**
     * Remove the specified gallery from storage.
     */
    public function destroy(DestroyGalleryRequest $request, Gallery $gallery): Response
    {
        $gallery->delete();

        return response()->noContent();
    }
}
