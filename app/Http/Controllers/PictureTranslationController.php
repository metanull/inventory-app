<?php

namespace App\Http\Controllers;

use App\Http\Requests\PictureTranslation\IndexPictureTranslationRequest;
use App\Http\Requests\PictureTranslation\ShowPictureTranslationRequest;
use App\Http\Requests\PictureTranslation\StorePictureTranslationRequest;
use App\Http\Requests\PictureTranslation\UpdatePictureTranslationRequest;
use App\Http\Resources\PictureTranslationResource;
use App\Models\PictureTranslation;

class PictureTranslationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexPictureTranslationRequest $request)
    {
        $pictureTranslations = PictureTranslation::paginate();

        return PictureTranslationResource::collection($pictureTranslations);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePictureTranslationRequest $request)
    {
        $validated = $request->validated();

        $pictureTranslation = PictureTranslation::create($validated);

        return new PictureTranslationResource($pictureTranslation);
    }

    /**
     * Display the specified resource.
     */
    public function show(ShowPictureTranslationRequest $request, PictureTranslation $pictureTranslation)
    {
        return new PictureTranslationResource($pictureTranslation);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePictureTranslationRequest $request, PictureTranslation $pictureTranslation)
    {
        $validated = $request->validated();

        $pictureTranslation->update($validated);

        return new PictureTranslationResource($pictureTranslation);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PictureTranslation $pictureTranslation)
    {
        $pictureTranslation->delete();

        return response()->noContent();
    }
}
