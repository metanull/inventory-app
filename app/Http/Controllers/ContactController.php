<?php

namespace App\Http\Controllers;

use App\Http\Resources\ContactResource;
use App\Models\Contact;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ContactController extends Controller
{
    /**
     * Display a listing of contacts.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index()
    {
        $contacts = Contact::with(['translations'])->get();

        return ContactResource::collection($contacts);
    }

    /**
     * Store a newly created contact.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'internal_name' => 'required|string|unique:contacts,internal_name',
            'phone_number' => 'nullable|string|regex:/^\+?[0-9\s\-\(\)]+$/',
            'fax_number' => 'nullable|string|regex:/^\+?[0-9\s\-\(\)]+$/',
            'email' => 'nullable|email',
            'translations' => 'required|array|min:1',
            'translations.*.language_id' => 'required|exists:languages,id',
            'translations.*.label' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $contact = Contact::create([
            'internal_name' => $request->internal_name,
            'phone_number' => $request->phone_number,
            'fax_number' => $request->fax_number,
            'email' => $request->email,
            'backward_compatibility' => $request->backward_compatibility,
        ]);

        // Create translations
        foreach ($request->translations as $translationData) {
            $contact->translations()->create([
                'language_id' => $translationData['language_id'],
                'label' => $translationData['label'],
            ]);
        }

        return (new ContactResource($contact->load('translations')))->response()->setStatusCode(201);
    }

    /**
     * Display the specified contact.
     *
     * @return \App\Http\Resources\ContactResource
     */
    public function show(Contact $contact)
    {
        return new ContactResource($contact->load('translations'));
    }

    /**
     * Update the specified contact.
     *
     * @return \App\Http\Resources\ContactResource|\Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Contact $contact)
    {
        $validator = Validator::make($request->all(), [
            'internal_name' => 'required|string|unique:contacts,internal_name,'.$contact->id,
            'phone_number' => 'nullable|string|regex:/^\+?[0-9\s\-\(\)]+$/',
            'fax_number' => 'nullable|string|regex:/^\+?[0-9\s\-\(\)]+$/',
            'email' => 'nullable|email',
            'translations' => 'array|min:1',
            'translations.*.language_id' => 'required_with:translations|exists:languages,id',
            'translations.*.label' => 'required_with:translations|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $contact->update([
            'internal_name' => $request->internal_name,
            'phone_number' => $request->phone_number,
            'fax_number' => $request->fax_number,
            'email' => $request->email,
            'backward_compatibility' => $request->backward_compatibility,
        ]);

        // Update translations if provided
        if ($request->has('translations')) {
            // Delete existing translations
            $contact->translations()->delete();

            // Create new translations
            foreach ($request->translations as $translationData) {
                $contact->translations()->create([
                    'language_id' => $translationData['language_id'],
                    'label' => $translationData['label'],
                ]);
            }
        }

        return new ContactResource($contact->load('translations'));
    }

    /**
     * Remove the specified contact.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Contact $contact)
    {
        $contact->delete();

        return response()->noContent();
    }
}
