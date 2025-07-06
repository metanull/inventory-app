<?php

namespace App\Http\Controllers;

use App\Http\Resources\ContactTranslationResource;
use App\Models\ContactTranslation;
use Illuminate\Http\Request;

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
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'contact_id' => 'required|uuid|exists:contacts,id',
            'language_id' => 'required|string|exists:languages,id',
            'label' => 'required|string|max:255',
        ]);

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
     */
    public function update(Request $request, ContactTranslation $contactTranslation)
    {
        $data = $request->validate([
            'contact_id' => 'sometimes|uuid|exists:contacts,id',
            'language_id' => 'sometimes|string|exists:languages,id',
            'label' => 'sometimes|string|max:255',
        ]);

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
