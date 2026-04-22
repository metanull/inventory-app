<?php

namespace App\Http\Controllers\Web;

use App\Enums\Permission;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\IndexGlossarySpellingRequest;
use App\Http\Requests\Web\StoreGlossarySpellingRequest;
use App\Http\Requests\Web\UpdateGlossarySpellingRequest;
use App\Models\Glossary;
use App\Models\GlossarySpelling;
use App\Services\Web\GlossarySpellingIndexQuery;
use Illuminate\Contracts\View\View;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;

class GlossarySpellingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:'.Permission::VIEW_DATA->value)->only(['index', 'show']);
        $this->middleware('permission:'.Permission::CREATE_DATA->value)->only(['create', 'store']);
        $this->middleware('permission:'.Permission::UPDATE_DATA->value)->only(['edit', 'update']);
        $this->middleware('permission:'.Permission::DELETE_DATA->value)->only(['destroy']);
    }

    public function index(Glossary $glossary, IndexGlossarySpellingRequest $request, GlossarySpellingIndexQuery $glossarySpellingIndexQuery): View
    {
        $listState = $request->listState();

        return view('glossary-spelling.index', [
            'spellings' => $glossarySpellingIndexQuery->paginate($listState),
            'listState' => $listState,
            'glossary' => $glossary,
        ]);
    }

    public function create(Glossary $glossary): View
    {
        return view('glossary-spelling.create', compact('glossary'));
    }

    public function store(StoreGlossarySpellingRequest $request, Glossary $glossary): RedirectResponse
    {
        $data = $request->validated();
        $data['glossary_id'] = $glossary->id;

        try {
            $spelling = GlossarySpelling::create($data);

            return redirect()->route('glossaries.spellings.show', [$glossary, $spelling])
                ->with('success', 'Spelling created successfully');
        } catch (QueryException $e) {
            // Check if it's a unique constraint violation
            if ($e->getCode() === '23000' || str_contains($e->getMessage(), 'Duplicate entry') || str_contains($e->getMessage(), 'UNIQUE constraint')) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['spelling' => 'This spelling already exists for this language and glossary entry.']);
            }

            throw $e;
        }
    }

    public function show(Glossary $glossary, GlossarySpelling $spelling): View
    {
        $spelling->load('language');

        return view('glossary-spelling.show', compact('glossary', 'spelling'));
    }

    public function edit(Glossary $glossary, GlossarySpelling $spelling): View
    {
        $spelling->load('language');

        return view('glossary-spelling.edit', compact('glossary', 'spelling'));
    }

    public function update(UpdateGlossarySpellingRequest $request, Glossary $glossary, GlossarySpelling $spelling): RedirectResponse
    {
        try {
            $spelling->update($request->validated());

            return redirect()->route('glossaries.spellings.show', [$glossary, $spelling])
                ->with('success', 'Spelling updated successfully');
        } catch (QueryException $e) {
            // Check if it's a unique constraint violation
            if ($e->getCode() === '23000' || str_contains($e->getMessage(), 'Duplicate entry') || str_contains($e->getMessage(), 'UNIQUE constraint')) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['spelling' => 'This spelling already exists for this language and glossary entry.']);
            }

            throw $e;
        }
    }

    public function destroy(Glossary $glossary, GlossarySpelling $spelling): RedirectResponse
    {
        $spelling->delete();

        return redirect()->route('glossaries.spellings.index', $glossary)
            ->with('success', 'Spelling deleted successfully');
    }
}
