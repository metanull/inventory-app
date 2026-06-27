<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\IndexAuthorRequest;
use App\Http\Requests\Api\ShowAuthorRequest;
use App\Http\Requests\Api\StoreAuthorRequest;
use App\Http\Requests\Api\UpdateAuthorRequest;
use App\Http\Resources\AuthorResource;
use App\Models\Author;
use App\Support\Includes\AllowList;
use App\Support\Includes\IncludeParser;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class AuthorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexAuthorRequest $request): AnonymousResourceCollection
    {
        $includes = $request->getIncludeParams();
        $pagination = $request->getPaginationParams();

        $query = Author::query()->with($includes);
        $paginator = $query->paginate(
            $pagination['per_page'],
            ['*'],
            'page',
            $pagination['page']
        );

        return AuthorResource::collection($paginator);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAuthorRequest $request): AuthorResource
    {
        $validated = $request->validated();
        $author = Author::create($validated);
        $author->refresh();
        $includes = IncludeParser::fromRequest($request, AllowList::for('author'));
        $author->load($includes);

        return new AuthorResource($author);
    }

    /**
     * Display the specified resource.
     */
    public function show(ShowAuthorRequest $request, Author $author): AuthorResource
    {
        $includes = $request->getIncludeParams();
        $author->load($includes);

        return new AuthorResource($author);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAuthorRequest $request, Author $author): AuthorResource
    {
        $validated = $request->validated();
        $author->update($validated);
        $author->refresh();
        $includes = IncludeParser::fromRequest($request, AllowList::for('author'));
        $author->load($includes);

        return new AuthorResource($author);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Author $author): Response
    {
        $author->delete();

        return response()->noContent();
    }
}
