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
        $contacts = Contact::with(['languages'])->get();

        return ContactResource::collection($contacts);
    }

    /**
     * Store a newly created contact.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'internal_name' => 'required|string|unique:contacts,internal_name',
            'languages' => 'required|array|min:1',
            'languages.*.language_id' => 'required|exists:languages,id',
            'languages.*.label' => 'required|string',
            'phone_number' => 'nullable|string|regex:/^\+?[0-9\s\-\(\)]+$/',
            'fax_number' => 'nullable|string|regex:/^\+?[0-9\s\-\(\)]+$/',
            'email' => 'nullable|email',
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

        // Attach languages with labels
        foreach ($request->languages as $languageData) {
            $contact->languages()->attach($languageData['language_id'], [
                'label' => $languageData['label'],
            ]);
        }

        return (new ContactResource($contact->load('languages')))->response()->setStatusCode(201);
    }

    /**
     * Display the specified contact.
     *
     * @return \App\Http\Resources\ContactResource
     */
    public function show(Contact $contact)
    {
        return new ContactResource($contact->load('languages'));
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
            'languages' => 'array|min:1',
            'languages.*.language_id' => 'required_with:languages|exists:languages,id',
            'languages.*.label' => 'required_with:languages|string',
            'phone_number' => 'nullable|string|regex:/^\+?[0-9\s\-\(\)]+$/',
            'fax_number' => 'nullable|string|regex:/^\+?[0-9\s\-\(\)]+$/',
            'email' => 'nullable|email',
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

        // Update languages if provided
        if ($request->has('languages')) {
            // First detach all existing languages
            $contact->languages()->detach();

            // Attach new languages with labels
            foreach ($request->languages as $languageData) {
                $contact->languages()->attach($languageData['language_id'], [
                    'label' => $languageData['label'],
                ]);
            }
        }

        return new ContactResource($contact->load('languages'));
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
