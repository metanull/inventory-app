<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\IndexLanguageTranslationRequest;
use App\Http\Requests\Api\ShowLanguageTranslationRequest;
use App\Http\Requests\Api\StoreLanguageTranslationRequest;
use App\Http\Requests\Api\UpdateLanguageTranslationRequest;
use App\Http\Resources\LanguageTranslationResource;
use App\Models\LanguageTranslation;
use App\Support\Includes\AllowList;
use App\Support\Includes\IncludeParser;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class LanguageTranslationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexLanguageTranslationRequest $request): AnonymousResourceCollection
    {
        $includes = $request->getIncludeParams();
        $pagination = $request->getPaginationParams();

        $query = LanguageTranslation::query()->with($includes);
        $paginator = $query->paginate(
            $pagination['per_page'],
            ['*'],
            'page',
            $pagination['page']
        );

        return LanguageTranslationResource::collection($paginator);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreLanguageTranslationRequest $request): LanguageTranslationResource
    {
        $validated = $request->validated();
        $languageTranslation = LanguageTranslation::create($validated);
        $languageTranslation->refresh();
        $includes = IncludeParser::fromRequest($request, AllowList::for('language_translation'));
        $languageTranslation->load($includes);

        return new LanguageTranslationResource($languageTranslation);
    }

    /**
     * Display the specified resource.
     */
    public function show(ShowLanguageTranslationRequest $request, LanguageTranslation $languageTranslation): LanguageTranslationResource
    {
        $includes = $request->getIncludeParams();
        $languageTranslation->load($includes);

        return new LanguageTranslationResource($languageTranslation);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateLanguageTranslationRequest $request, LanguageTranslation $languageTranslation): LanguageTranslationResource
    {
        $validated = $request->validated();
        $languageTranslation->update($validated);
        $languageTranslation->refresh();
        $includes = IncludeParser::fromRequest($request, AllowList::for('language_translation'));
        $languageTranslation->load($includes);

        return new LanguageTranslationResource($languageTranslation);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LanguageTranslation $languageTranslation): Response
    {
        $languageTranslation->delete();

        return response()->noContent();
    }
}
