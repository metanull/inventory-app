<?php

namespace App\Http\Controllers;

use App\Http\Resources\ExhibitionResource;
use App\Models\Exhibition;
use App\Support\Includes\AllowList;
use App\Support\Includes\IncludeParser;
use App\Support\Pagination\PaginationParams;
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
    public function store(Request $request)
    {
        $validated = $request->validate([
            'internal_name' => ['required', 'string', 'unique:exhibitions,internal_name'],
            'backward_compatibility' => ['nullable', 'string'],
        ]);
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
    public function show(Request $request, Exhibition $exhibition)
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
    public function update(Request $request, Exhibition $exhibition)
    {
        $validated = $request->validate([
            'internal_name' => ['sometimes', 'string', Rule::unique('exhibitions', 'internal_name')->ignore($exhibition->id, 'id')],
            'backward_compatibility' => ['nullable', 'string'],
        ]);
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
    public function destroy(Exhibition $exhibition)
    {
        $exhibition->delete();

        return response()->noContent();
    }
}
