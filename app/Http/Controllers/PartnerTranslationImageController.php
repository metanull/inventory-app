<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\IndexPartnerTranslationImageRequest;
use App\Http\Requests\Api\ShowPartnerTranslationImageRequest;
use App\Http\Requests\Api\StorePartnerTranslationImageRequest;
use App\Http\Requests\Api\UpdatePartnerTranslationImageRequest;
use App\Http\Resources\PartnerTranslationImageResource;
use App\Models\PartnerTranslationImage;
use App\Support\Includes\AllowList;
use App\Support\Includes\IncludeParser;

class PartnerTranslationImageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexPartnerTranslationImageRequest $request)
    {
        $includes = $request->getIncludeParams();
        $pagination = $request->getPaginationParams();

        $query = PartnerTranslationImage::query()->with($includes);
        $paginator = $query->paginate(
            $pagination['per_page'],
            ['*'],
            'page',
            $pagination['page']
        );

        return PartnerTranslationImageResource::collection($paginator);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return PartnerTranslationImageResource
     */
    public function store(StorePartnerTranslationImageRequest $request)
    {
        $validated = $request->validated();
        $partnerTranslationImage = PartnerTranslationImage::create($validated);
        $partnerTranslationImage->refresh();
        $includes = IncludeParser::fromRequest($request, AllowList::for('partner_translation_image'));
        $partnerTranslationImage->load($includes);

        return new PartnerTranslationImageResource($partnerTranslationImage);
    }

    /**
     * Display the specified resource.
     */
    public function show(ShowPartnerTranslationImageRequest $request, PartnerTranslationImage $partnerTranslationImage)
    {
        $includes = $request->getIncludeParams();
        $partnerTranslationImage->load($includes);

        return new PartnerTranslationImageResource($partnerTranslationImage);
    }

    /**
     * Update the specified resource in storage.
     *
     * @return PartnerTranslationImageResource
     */
    public function update(UpdatePartnerTranslationImageRequest $request, PartnerTranslationImage $partnerTranslationImage)
    {
        $validated = $request->validated();
        $partnerTranslationImage->update($validated);
        $partnerTranslationImage->refresh();
        $includes = IncludeParser::fromRequest($request, AllowList::for('partner_translation_image'));
        $partnerTranslationImage->load($includes);

        return new PartnerTranslationImageResource($partnerTranslationImage);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PartnerTranslationImage $partnerTranslationImage)
    {
        $partnerTranslationImage->delete();

        return response()->noContent();
    }
}
