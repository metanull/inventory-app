<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\IndexContactRequest;
use App\Http\Requests\Api\ShowContactRequest;
use App\Http\Requests\Api\StoreContactRequest;
use App\Http\Requests\Api\UpdateContactRequest;
use App\Http\Resources\ContactResource;
use App\Models\Contact;
use App\Support\Includes\AllowList;
use App\Support\Includes\IncludeParser;
use Illuminate\Http\JsonResponse;

class ContactController extends Controller
{
    /**
     * Display a listing of contacts.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(IndexContactRequest $request)
    {
        $includes = $request->getIncludeParams();
        $pagination = $request->getPaginationParams();

        // Default includes expected by tests/clients
        $defaults = ['translations'];
        $with = array_values(array_unique(array_merge($defaults, $includes)));

        $query = Contact::query()->with($with);

        $paginator = $query->paginate(
            $pagination['per_page'],
            ['*'],
            'page',
            $pagination['page']
        );

        return ContactResource::collection($paginator);
    }

    /**
     * Store a newly created contact.
     */
    public function store(StoreContactRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $contact = Contact::create([
            'internal_name' => $validated['internal_name'],
            'phone_number' => $validated['phone_number'] ?? null,
            'fax_number' => $validated['fax_number'] ?? null,
            'email' => $validated['email'] ?? null,
            'backward_compatibility' => $validated['backward_compatibility'] ?? null,
        ]);

        // Create translations
        foreach ($validated['translations'] as $translationData) {
            $contact->translations()->create([
                'language_id' => $translationData['language_id'],
                'label' => $translationData['label'],
            ]);
        }

        // Load default includes (translations) plus any explicitly requested
        $requested = IncludeParser::fromRequest($request, AllowList::for('contact'));
        $defaults = ['translations'];
        $contact->load(array_values(array_unique(array_merge($defaults, $requested))));

        return (new ContactResource($contact))->response()->setStatusCode(201);
    }

    /**
     * Display the specified contact.
     *
     * @return \App\Http\Resources\ContactResource
     */
    public function show(ShowContactRequest $request, Contact $contact)
    {
        $includes = $request->getIncludeParams();
        if (! empty($includes)) {
            $contact->load($includes);
        }

        return new ContactResource($contact);
    }

    /**
     * Update the specified contact.
     *
     * @return \App\Http\Resources\ContactResource
     */
    public function update(UpdateContactRequest $request, Contact $contact)
    {
        $validated = $request->validated();

        $contact->update([
            'internal_name' => $validated['internal_name'],
            'phone_number' => $validated['phone_number'] ?? null,
            'fax_number' => $validated['fax_number'] ?? null,
            'email' => $validated['email'] ?? null,
            'backward_compatibility' => $validated['backward_compatibility'] ?? null,
        ]);

        // Update translations if provided
        if (isset($validated['translations'])) {
            // Delete existing translations
            $contact->translations()->delete();

            // Create new translations
            foreach ($validated['translations'] as $translationData) {
                $contact->translations()->create([
                    'language_id' => $translationData['language_id'],
                    'label' => $translationData['label'],
                ]);
            }
        }

        // Load default includes (translations) plus any explicitly requested
        $requested = IncludeParser::fromRequest($request, AllowList::for('contact'));
        $defaults = ['translations'];
        $contact->load(array_values(array_unique(array_merge($defaults, $requested))));

        return new ContactResource($contact);
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
