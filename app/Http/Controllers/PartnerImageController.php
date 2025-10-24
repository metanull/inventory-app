<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\IndexPartnerImageRequest;
use App\Http\Requests\Api\ShowPartnerImageRequest;
use App\Http\Requests\Api\StorePartnerImageRequest;
use App\Http\Requests\Api\UpdatePartnerImageRequest;
use App\Http\Resources\PartnerImageResource;
use App\Models\PartnerImage;
use App\Support\Includes\AllowList;
use App\Support\Includes\IncludeParser;

class PartnerImageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexPartnerImageRequest $request)
    {
        $includes = $request->getIncludeParams();
        $pagination = $request->getPaginationParams();

        $query = PartnerImage::query()->with($includes);
        $paginator = $query->paginate(
            $pagination['per_page'],
            ['*'],
            'page',
            $pagination['page']
        );

        return PartnerImageResource::collection($paginator);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return PartnerImageResource
     */
    public function store(StorePartnerImageRequest $request)
    {
        $validated = $request->validated();
        $partnerImage = PartnerImage::create($validated);
        $partnerImage->refresh();
        $includes = IncludeParser::fromRequest($request, AllowList::for('partner_image'));
        $partnerImage->load($includes);

        return new PartnerImageResource($partnerImage);
    }

    /**
     * Display the specified resource.
     */
    public function show(ShowPartnerImageRequest $request, PartnerImage $partnerImage)
    {
        $includes = $request->getIncludeParams();
        $partnerImage->load($includes);

        return new PartnerImageResource($partnerImage);
    }

    /**
     * Update the specified resource in storage.
     *
     * @return PartnerImageResource
     */
    public function update(UpdatePartnerImageRequest $request, PartnerImage $partnerImage)
    {
        $validated = $request->validated();
        $partnerImage->update($validated);
        $partnerImage->refresh();
        $includes = IncludeParser::fromRequest($request, AllowList::for('partner_image'));
        $partnerImage->load($includes);

        return new PartnerImageResource($partnerImage);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PartnerImage $partnerImage)
    {
        $partnerImage->delete();

        return response()->noContent();
    }
}
