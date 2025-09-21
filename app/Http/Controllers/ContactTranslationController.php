<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactTranslation\IndexContactTranslationRequest;
use App\Http\Requests\ContactTranslation\ShowContactTranslationRequest;
use App\Http\Requests\ContactTranslation\StoreContactTranslationRequest;
use App\Http\Requests\ContactTranslation\UpdateContactTranslationRequest;
use App\Http\Resources\ContactTranslationResource;
use App\Models\ContactTranslation;

class ContactTranslationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexContactTranslationRequest $request)
    {
        return ContactTranslationResource::collection(ContactTranslation::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreContactTranslationRequest $request)
    {
        $translation = ContactTranslation::create($request->validated());

        return new ContactTranslationResource($translation);
    }

    /**
     * Display the specified resource.
     */
    public function show(ShowContactTranslationRequest $request, ContactTranslation $contactTranslation)
    {
        return new ContactTranslationResource($contactTranslation);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateContactTranslationRequest $request, ContactTranslation $contactTranslation)
    {
        $contactTranslation->update($request->validated());

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
