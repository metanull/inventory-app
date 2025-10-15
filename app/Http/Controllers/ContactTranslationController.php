<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\StoreContactTranslationRequest;
use App\Http\Requests\Api\UpdateContactTranslationRequest;
use App\Http\Resources\ContactTranslationResource;
use App\Models\ContactTranslation;

class ContactTranslationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return ContactTranslationResource::collection(ContactTranslation::all());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return ContactTranslationResource
     */
    public function store(StoreContactTranslationRequest $request)
    {
        $data = $request->validated();

        $translation = ContactTranslation::create($data);

        return new ContactTranslationResource($translation);
    }

    /**
     * Display the specified resource.
     */
    public function show(ContactTranslation $contactTranslation)
    {
        return new ContactTranslationResource($contactTranslation);
    }

    /**
     * Update the specified resource in storage.
     *
     * @return ContactTranslationResource
     */
    public function update(UpdateContactTranslationRequest $request, ContactTranslation $contactTranslation)
    {
        $data = $request->validated();

        $contactTranslation->update($data);

        return new ContactTranslationResource($contactTranslation);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ContactTranslation $contactTranslation)
    {
        $contactTranslation->delete();

        return response()->noContent();
    }
}
