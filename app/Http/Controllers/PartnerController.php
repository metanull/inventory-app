<?php

namespace App\Http\Controllers;

use App\Http\Resources\PartnerResource;
use App\Models\Partner;
use App\Support\Includes\AllowList;
use App\Support\Includes\IncludeParser;
use App\Support\Pagination\PaginationParams;
use Illuminate\Http\Request;

class PartnerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $includes = IncludeParser::fromRequest($request, AllowList::for('partner'));
        $pagination = PaginationParams::fromRequest($request);

        $query = Partner::query()->with($includes);
        $paginator = $query->paginate(
            $pagination['per_page'],
            ['*'],
            'page',
            $pagination['page']
        );

        return PartnerResource::collection($paginator);
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
        $includes = IncludeParser::fromRequest($request, AllowList::for('partner'));
        $partner->load($includes);

        return new PartnerResource($partner);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Partner $partner)
    {
        $includes = IncludeParser::fromRequest($request, AllowList::for('partner'));
        $partner->load($includes);

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
            'internal_name' => 'required|string',
            'backward_compatibility' => 'nullable|string',
            'type' => 'required|in:museum,institution,individual',
            'country_id' => 'nullable|string|size:3',
        ]);
        $partner->update($validated);
        $partner->refresh();
        $includes = IncludeParser::fromRequest($request, AllowList::for('partner'));
        $partner->load($includes);

        return new PartnerResource($partner);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Partner $partner)
    {
        $partner->delete();

        return response()->noContent();
    }
}
