<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\IndexPartnerLogoRequest;
use App\Http\Requests\Api\ShowPartnerLogoRequest;
use App\Http\Requests\Api\StorePartnerLogoRequest;
use App\Http\Requests\Api\UpdatePartnerLogoRequest;
use App\Http\Resources\PartnerLogoResource;
use App\Models\PartnerLogo;
use App\Support\Includes\AllowList;
use App\Support\Includes\IncludeParser;

class PartnerLogoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexPartnerLogoRequest $request)
    {
        $includes = $request->getIncludeParams();
        $pagination = $request->getPaginationParams();

        $query = PartnerLogo::query()->with($includes);
        $paginator = $query->paginate(
            $pagination['per_page'],
            ['*'],
            'page',
            $pagination['page']
        );

        return PartnerLogoResource::collection($paginator);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return PartnerLogoResource
     */
    public function store(StorePartnerLogoRequest $request)
    {
        $validated = $request->validated();
        $partnerLogo = PartnerLogo::create($validated);
        $partnerLogo->refresh();
        $includes = IncludeParser::fromRequest($request, AllowList::for('partner_logo'));
        $partnerLogo->load($includes);

        return new PartnerLogoResource($partnerLogo);
    }

    /**
     * Display the specified resource.
     */
    public function show(ShowPartnerLogoRequest $request, PartnerLogo $partnerLogo)
    {
        $includes = $request->getIncludeParams();
        $partnerLogo->load($includes);

        return new PartnerLogoResource($partnerLogo);
    }

    /**
     * Update the specified resource in storage.
     *
     * @return PartnerLogoResource
     */
    public function update(UpdatePartnerLogoRequest $request, PartnerLogo $partnerLogo)
    {
        $validated = $request->validated();
        $partnerLogo->update($validated);
        $partnerLogo->refresh();
        $includes = IncludeParser::fromRequest($request, AllowList::for('partner_logo'));
        $partnerLogo->load($includes);

        return new PartnerLogoResource($partnerLogo);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PartnerLogo $partnerLogo)
    {
        $partnerLogo->delete();

        return response()->noContent();
    }

    /**
     * Move partner logo up in display order.
     */
    public function moveUp(PartnerLogo $partnerLogo)
    {
        $partnerLogo->moveUp();

        // Refresh the model to get updated data
        $partnerLogo->refresh();

        return new PartnerLogoResource($partnerLogo);
    }

    /**
     * Move partner logo down in display order.
     */
    public function moveDown(PartnerLogo $partnerLogo)
    {
        $partnerLogo->moveDown();

        // Refresh the model to get updated data
        $partnerLogo->refresh();

        return new PartnerLogoResource($partnerLogo);
    }

    /**
     * Tighten ordering for all logos of the partner.
     */
    public function tightenOrdering(PartnerLogo $partnerLogo)
    {
        $partnerLogo->tightenOrderingForPartner();

        return new \App\Http\Resources\OperationSuccessResource([
            'success' => true,
            'message' => 'Logo ordering tightened successfully',
        ]);
    }

    /**
     * Returns the file to the caller.
     */
    public function download(PartnerLogo $partnerLogo)
    {
        $disk = config('localstorage.pictures.disk');
        $directory = trim(config('localstorage.pictures.directory'), '/');
        $filename = $partnerLogo->original_name ?: basename($partnerLogo->path);

        // Prepend directory to path
        $storagePath = $directory.'/'.$partnerLogo->path;

        return \App\Http\Responses\FileResponse::download(
            $disk,
            $storagePath,
            $filename,
            $partnerLogo->mime_type
        );
    }

    /**
     * Returns the logo file for direct viewing (e.g., for use in <img> src attribute).
     */
    public function view(PartnerLogo $partnerLogo)
    {
        $disk = config('localstorage.pictures.disk');
        $directory = trim(config('localstorage.pictures.directory'), '/');

        // Prepend directory to path
        $storagePath = $directory.'/'.$partnerLogo->path;

        return \App\Http\Responses\FileResponse::view(
            $disk,
            $storagePath,
            $partnerLogo->mime_type
        );
    }
}
