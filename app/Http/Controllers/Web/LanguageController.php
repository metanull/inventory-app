<?php

namespace App\Http\Controllers\Web;

use App\Enums\Permission;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\StoreLanguageRequest;
use App\Http\Requests\Web\UpdateLanguageRequest;
use App\Models\Language;
use App\Support\Web\SearchAndPaginate;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LanguageController extends Controller
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
        /** @var LengthAwarePaginator $languages */
        [$languages, $search] = $this->searchAndPaginate(Language::query(), $request);

        return view('languages.index', compact('languages', 'search'));
    }

    public function show(Language $language): View
    {
        return view('languages.show', compact('language'));
    }

    public function create(): View
    {
        return view('languages.create');
    }

    public function store(StoreLanguageRequest $request): RedirectResponse
    {
        $payload = $request->validated();
        $makeDefault = (bool) ($payload['is_default'] ?? false);
        unset($payload['is_default']);
        $language = Language::create($payload);
        if ($makeDefault) {
            $language->setDefault();
        }

        return redirect()->route('languages.show', $language)->with('success', 'Language created successfully');
    }

    public function edit(Language $language): View
    {
        return view('languages.edit', compact('language'));
    }

    public function update(UpdateLanguageRequest $request, Language $language): RedirectResponse
    {
        $payload = $request->validated();
        $makeDefault = (bool) ($payload['is_default'] ?? false);
        unset($payload['is_default']);
        $language->update($payload);
        if ($makeDefault) {
            $language->setDefault();
        } elseif ($language->is_default && ! $makeDefault) {
            // If checkbox not sent we do nothing; explicit un-setting could be future feature.
        }

        return redirect()->route('languages.show', $language)->with('success', 'Language updated successfully');
    }

    public function destroy(Language $language): RedirectResponse
    {
        // Prevent deleting default language (basic guard)
        if ($language->is_default) {
            return redirect()->route('languages.show', $language)->with('error', 'Cannot delete the default language.');
        }
        $language->delete();

        return redirect()->route('languages.index')->with('success', 'Language deleted successfully');
    }
}
