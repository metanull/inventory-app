<?php

namespace App\Http\Controllers\Web;

use App\Enums\Permission;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\StoreAddressRequest;
use App\Http\Requests\Web\UpdateAddressRequest;
use App\Models\Address;
use App\Models\Country;
use App\Models\Language;
use App\Support\Web\SearchAndPaginate;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AddressController extends Controller
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
        $query = Address::with(['country', 'translations']);
        [$addresses, $search] = $this->searchAndPaginate($query, $request);

        return view('addresses.index', compact('addresses', 'search'));
    }

    public function show(Address $address): View
    {
        $address->load(['country', 'translations.language']);

        return view('addresses.show', compact('address'));
    }

    public function create(): View
    {
        $countries = Country::orderBy('internal_name')->get();
        $languages = Language::orderBy('internal_name')->get();

        return view('addresses.create', compact('countries', 'languages'));
    }

    public function store(StoreAddressRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $address = Address::create([
            'internal_name' => $validated['internal_name'],
            'country_id' => $validated['country_id'],
        ]);

        // Create translations if provided
        if (isset($validated['translations'])) {
            foreach ($validated['translations'] as $translation) {
                if (! empty($translation['language_id']) && ! empty($translation['address'])) {
                    $address->translations()->create([
                        'language_id' => $translation['language_id'],
                        'address' => $translation['address'],
                        'description' => $translation['description'] ?? null,
                    ]);
                }
            }
        }

        return redirect()->route('addresses.show', $address)->with('success', 'Address created successfully');
    }

    public function edit(Address $address): View
    {
        $address->load('translations');
        $countries = Country::orderBy('internal_name')->get();
        $languages = Language::orderBy('internal_name')->get();

        return view('addresses.edit', compact('address', 'countries', 'languages'));
    }

    public function update(UpdateAddressRequest $request, Address $address): RedirectResponse
    {
        $validated = $request->validated();

        $address->update([
            'internal_name' => $validated['internal_name'],
            'country_id' => $validated['country_id'],
        ]);

        // Update translations
        if (isset($validated['translations'])) {
            // Delete existing translations
            $address->translations()->delete();

            // Create new translations
            foreach ($validated['translations'] as $translation) {
                if (! empty($translation['language_id']) && ! empty($translation['address'])) {
                    $address->translations()->create([
                        'language_id' => $translation['language_id'],
                        'address' => $translation['address'],
                        'description' => $translation['description'] ?? null,
                    ]);
                }
            }
        }

        return redirect()->route('addresses.show', $address)->with('success', 'Address updated successfully');
    }

    public function destroy(Address $address): RedirectResponse
    {
        $address->delete();

        return redirect()->route('addresses.index')->with('success', 'Address deleted successfully');
    }
}
