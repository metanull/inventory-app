<?php

namespace App\Http\Controllers;

use App\Http\Requests\Exhibition\DestroyExhibitionRequest;
use App\Http\Requests\Exhibition\IndexExhibitionRequest;
use App\Http\Requests\Exhibition\ShowExhibitionRequest;
use App\Http\Requests\Exhibition\StoreExhibitionRequest;
use App\Http\Requests\Exhibition\UpdateExhibitionRequest;
use App\Http\Resources\ExhibitionResource;
use App\Models\Exhibition;
use App\Support\Includes\AllowList;
use App\Support\Includes\IncludeParser;
use App\Support\Pagination\PaginationParams;
use Illuminate\Routing\Controller;

class ExhibitionController extends Controller
{
    /**
     * Display a listing of the exhibitions.
     */
    public function index(IndexExhibitionRequest $request)
    {
        $includes = IncludeParser::fromRequest($request, AllowList::for('exhibition'));
        $pagination = PaginationParams::fromRequest($request);

        $query = Exhibition::query();
        if (! empty($includes)) {
            $query->with($includes);
        }

        $paginator = $query->paginate(
            $pagination['per_page'],
            ['*'],
            'page',
            $pagination['page']
        );

        return ExhibitionResource::collection($paginator);
    }

    /**
     * Store a newly created exhibition in storage.
     */
    public function store(StoreExhibitionRequest $request)
    {
        $validated = $request->validated();
        $exhibition = Exhibition::create($validated);
        $requested = IncludeParser::fromRequest($request, AllowList::for('exhibition'));
        if (! empty($requested)) {
            $exhibition->load($requested);
        }

        return new ExhibitionResource($exhibition);
    }

    /**
     * Display the specified exhibition.
     */
    public function show(ShowExhibitionRequest $request, Exhibition $exhibition)
    {
        $includes = IncludeParser::fromRequest($request, AllowList::for('exhibition'));
        if (! empty($includes)) {
            $exhibition->load($includes);
        }

        return new ExhibitionResource($exhibition);
    }

    /**
     * Update the specified exhibition in storage.
     */
    public function update(UpdateExhibitionRequest $request, Exhibition $exhibition)
    {
        $validated = $request->validated();
        $exhibition->update($validated);
        $requested = IncludeParser::fromRequest($request, AllowList::for('exhibition'));
        if (! empty($requested)) {
            $exhibition->load($requested);
        }

        return new ExhibitionResource($exhibition);
    }

    /**
     * Remove the specified exhibition from storage.
     */
    public function destroy(DestroyExhibitionRequest $request, Exhibition $exhibition)
    {
        $exhibition->delete();

        return response()->noContent();
    }
}
