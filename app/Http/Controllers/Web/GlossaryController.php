<?php

namespace App\Http\Controllers\Web;

use App\Enums\Permission;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\StoreGlossaryRequest;
use App\Http\Requests\Web\UpdateGlossaryRequest;
use App\Models\Glossary;
use App\Support\Web\SearchAndPaginate;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class GlossaryController extends Controller
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
        [$glossaries, $search] = $this->searchAndPaginate(Glossary::query(), $request);

        return view('glossary.index', compact('glossaries', 'search'));
    }

    public function show(Glossary $glossary): View
    {
        $glossary->load(['translations.language', 'spellings.language', 'synonyms']);

        return view('glossary.show', compact('glossary'));
    }

    public function create(): View
    {
        return view('glossary.create');
    }

    public function store(StoreGlossaryRequest $request): RedirectResponse
    {
        $glossary = Glossary::create($request->validated());

        return redirect()->route('glossaries.show', $glossary)->with('success', 'Glossary entry created successfully');
    }

    public function edit(Glossary $glossary): View
    {
        $glossary->load(['translations.language', 'spellings.language', 'synonyms']);

        return view('glossary.edit', compact('glossary'));
    }

    public function update(UpdateGlossaryRequest $request, Glossary $glossary): RedirectResponse
    {
        $glossary->update($request->validated());

        return redirect()->route('glossaries.show', $glossary)->with('success', 'Glossary entry updated successfully');
    }

    public function destroy(Glossary $glossary): RedirectResponse
    {
        $glossary->delete();

        return redirect()->route('glossaries.index')->with('success', 'Glossary entry deleted successfully');
    }
}
