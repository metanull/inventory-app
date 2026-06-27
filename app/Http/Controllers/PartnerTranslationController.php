<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\IndexPartnerTranslationRequest;
use App\Http\Requests\Api\ShowPartnerTranslationRequest;
use App\Http\Requests\Api\StorePartnerTranslationRequest;
use App\Http\Requests\Api\UpdatePartnerTranslationRequest;
use App\Http\Resources\PartnerTranslationResource;
use App\Models\PartnerTranslation;
use App\Support\Includes\AllowList;
use App\Support\Includes\IncludeParser;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class PartnerTranslationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexPartnerTranslationRequest $request): AnonymousResourceCollection
    {
        $includes = $request->getIncludeParams();
        $pagination = $request->getPaginationParams();

        $query = PartnerTranslation::query()->with($includes);
        $paginator = $query->paginate(
            $pagination['per_page'],
            ['*'],
            'page',
            $pagination['page']
        );

        return PartnerTranslationResource::collection($paginator);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePartnerTranslationRequest $request): PartnerTranslationResource
    {
        $validated = $request->validated();
        $partnerTranslation = PartnerTranslation::create($validated);
        $partnerTranslation->refresh();
        $includes = IncludeParser::fromRequest($request, AllowList::for('partner_translation'));
        $partnerTranslation->load($includes);

        return new PartnerTranslationResource($partnerTranslation);
    }

    /**
     * Display the specified resource.
     */
    public function show(ShowPartnerTranslationRequest $request, PartnerTranslation $partnerTranslation): PartnerTranslationResource
    {
        $includes = $request->getIncludeParams();
        $partnerTranslation->load($includes);

        return new PartnerTranslationResource($partnerTranslation);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePartnerTranslationRequest $request, PartnerTranslation $partnerTranslation): PartnerTranslationResource
    {
        $validated = $request->validated();
        $partnerTranslation->update($validated);
        $partnerTranslation->refresh();
        $includes = IncludeParser::fromRequest($request, AllowList::for('partner_translation'));
        $partnerTranslation->load($includes);

        return new PartnerTranslationResource($partnerTranslation);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PartnerTranslation $partnerTranslation): Response
    {
        $partnerTranslation->delete();

        return response()->noContent();
    }
}
