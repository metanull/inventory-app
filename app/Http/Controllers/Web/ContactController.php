<?php

namespace App\Http\Controllers\Web;

use App\Enums\Permission;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\StoreContactRequest;
use App\Http\Requests\Web\UpdateContactRequest;
use App\Models\Contact;
use App\Models\Language;
use App\Support\Web\SearchAndPaginate;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    use SearchAndPaginate;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:'.Permission::VIEW_DATA->value)->only(['index', 'show']);
        $this->middleware('permission:'.Permission::CREATE_DATA->value)->only(['create', 'store']);
        $this->middleware('permission:'.Permission::UPDATE_DATA->value)->only(['edit', 'update']);
        $this->middleware('permission:'.Permission::DELETE_DATA->value)->only(['destroy']);
    }

    public function index(Request $request): View
    {
        $query = Contact::with('translations');
        [$contacts, $search] = $this->searchAndPaginate($query, $request);

        return view('contacts.index', compact('contacts', 'search'));
    }

    public function show(Contact $contact): View
    {
        $contact->load('translations.language');

        return view('contacts.show', compact('contact'));
    }

    public function create(): View
    {
        $languages = Language::orderBy('internal_name')->get();

        return view('contacts.create', compact('languages'));
    }

    public function store(StoreContactRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $contact = Contact::create([
            'internal_name' => $validated['internal_name'],
            'phone_number' => $validated['phone_number'] ?? null,
            'fax_number' => $validated['fax_number'] ?? null,
            'email' => $validated['email'] ?? null,
        ]);

        // Create translations if provided
        if (isset($validated['translations'])) {
            foreach ($validated['translations'] as $translation) {
                if (! empty($translation['language_id']) && ! empty($translation['label'])) {
                    $contact->translations()->create([
                        'language_id' => $translation['language_id'],
                        'label' => $translation['label'],
                    ]);
                }
            }
        }

        return redirect()->route('contacts.show', $contact)->with('success', 'Contact created successfully');
    }

    public function edit(Contact $contact): View
    {
        $contact->load('translations');
        $languages = Language::orderBy('internal_name')->get();

        return view('contacts.edit', compact('contact', 'languages'));
    }

    public function update(UpdateContactRequest $request, Contact $contact): RedirectResponse
    {
        $validated = $request->validated();

        $contact->update([
            'internal_name' => $validated['internal_name'],
            'phone_number' => $validated['phone_number'] ?? null,
            'fax_number' => $validated['fax_number'] ?? null,
            'email' => $validated['email'] ?? null,
        ]);

        // Update translations
        if (isset($validated['translations'])) {
            // Delete existing translations
            $contact->translations()->delete();

            // Create new translations
            foreach ($validated['translations'] as $translation) {
                if (! empty($translation['language_id']) && ! empty($translation['label'])) {
                    $contact->translations()->create([
                        'language_id' => $translation['language_id'],
                        'label' => $translation['label'],
                    ]);
                }
            }
        }

        return redirect()->route('contacts.show', $contact)->with('success', 'Contact updated successfully');
    }

    public function destroy(Contact $contact): RedirectResponse
    {
        $contact->delete();

        return redirect()->route('contacts.index')->with('success', 'Contact deleted successfully');
    }
}
