<?php

namespace App\Http\Controllers\Web;

use App\Enums\Permission;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\IndexGlossaryRequest;
use App\Http\Requests\Web\StoreGlossaryRequest;
use App\Http\Requests\Web\UpdateGlossaryRequest;
use App\Models\Glossary;
use App\Services\Web\GlossaryIndexQuery;
use App\Services\Web\TranslationSectionData;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class GlossaryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:'.Permission::VIEW_DATA->value)->only(['index', 'show']);
        $this->middleware('permission:'.Permission::CREATE_DATA->value)->only(['create', 'store']);
        $this->middleware('permission:'.Permission::UPDATE_DATA->value)->only(['edit', 'update']);
        $this->middleware('permission:'.Permission::DELETE_DATA->value)->only(['destroy']);
    }

    public function index(IndexGlossaryRequest $request, GlossaryIndexQuery $glossaryIndexQuery): View
    {
        $listState = $request->listState();

        return view('glossaries.index', [
            'glossaries' => $glossaryIndexQuery->paginate($listState),
            'listState' => $listState,
        ]);
    }

    public function show(Glossary $glossary, TranslationSectionData $translationSectionData): View
    {
        $glossary->load(['translations.language', 'spellings.language', 'synonyms']);

        return view('glossaries.show', [
            'glossary' => $glossary,
            'translationGroups' => $translationSectionData->build($glossary->translations, false),
        ]);
    }

    public function create(): View
    {
        return view('glossaries.create');
    }

    public function store(StoreGlossaryRequest $request): RedirectResponse
    {
        $glossary = Glossary::create($request->validated());

        return redirect()->route('glossaries.show', $glossary)->with('success', 'Glossary entry created successfully');
    }

    public function edit(Glossary $glossary): View
    {
        $glossary->load(['translations.language', 'spellings.language', 'synonyms']);

        return view('glossaries.edit', compact('glossary'));
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
