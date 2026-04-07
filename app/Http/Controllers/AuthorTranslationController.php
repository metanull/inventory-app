<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\IndexAuthorTranslationRequest;
use App\Http\Requests\Api\ShowAuthorTranslationRequest;
use App\Http\Requests\Api\StoreAuthorTranslationRequest;
use App\Http\Requests\Api\UpdateAuthorTranslationRequest;
use App\Http\Resources\AuthorTranslationResource;
use App\Models\AuthorTranslation;
use App\Support\Includes\AllowList;
use App\Support\Includes\IncludeParser;

class AuthorTranslationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexAuthorTranslationRequest $request)
    {
        $includes = $request->getIncludeParams();
        $pagination = $request->getPaginationParams();

        $query = AuthorTranslation::query()->with($includes);
        $paginator = $query->paginate(
            $pagination['per_page'],
            ['*'],
            'page',
            $pagination['page']
        );

        return AuthorTranslationResource::collection($paginator);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return AuthorTranslationResource
     */
    public function store(StoreAuthorTranslationRequest $request)
    {
        $validated = $request->validated();
        $authorTranslation = AuthorTranslation::create($validated);
        $authorTranslation->refresh();
        $includes = IncludeParser::fromRequest($request, AllowList::for('author_translation'));
        $authorTranslation->load($includes);

        return new AuthorTranslationResource($authorTranslation);
    }

    /**
     * Display the specified resource.
     */
    public function show(ShowAuthorTranslationRequest $request, AuthorTranslation $authorTranslation)
    {
        $includes = $request->getIncludeParams();
        $authorTranslation->load($includes);

        return new AuthorTranslationResource($authorTranslation);
    }

    /**
     * Update the specified resource in storage.
     *
     * @return AuthorTranslationResource
     */
    public function update(UpdateAuthorTranslationRequest $request, AuthorTranslation $authorTranslation)
    {
        $validated = $request->validated();
        $authorTranslation->update($validated);
        $authorTranslation->refresh();
        $includes = IncludeParser::fromRequest($request, AllowList::for('author_translation'));
        $authorTranslation->load($includes);

        return new AuthorTranslationResource($authorTranslation);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AuthorTranslation $authorTranslation)
    {
        $authorTranslation->delete();

        return response()->noContent();
    }
}
