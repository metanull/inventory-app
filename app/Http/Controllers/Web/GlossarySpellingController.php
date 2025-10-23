<?php

namespace App\Http\Controllers\Web;

use App\Enums\Permission;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\StoreGlossarySpellingRequest;
use App\Http\Requests\Web\UpdateGlossarySpellingRequest;
use App\Models\Glossary;
use App\Models\GlossarySpelling;
use App\Models\Language;
use Illuminate\Contracts\View\View;
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

    public function index(Glossary $glossary): View
    {
        $spellings = $glossary->spellings()->with('language')->get();

        return view('glossary-spelling.index', compact('glossary', 'spellings'));
    }

    public function create(Glossary $glossary): View
    {
        $languages = Language::orderBy('internal_name')->get();

        return view('glossary-spelling.create', compact('glossary', 'languages'));
    }

    public function store(StoreGlossarySpellingRequest $request, Glossary $glossary): RedirectResponse
    {
        $data = $request->validated();
        $data['glossary_id'] = $glossary->id;

        try {
            $spelling = GlossarySpelling::create($data);

            return redirect()->route('glossaries.spellings.show', [$glossary, $spelling])
                ->with('success', 'Spelling created successfully');
        } catch (\Illuminate\Database\QueryException $e) {
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
        $languages = Language::orderBy('internal_name')->get();

        return view('glossary-spelling.edit', compact('glossary', 'spelling', 'languages'));
    }

    public function update(UpdateGlossarySpellingRequest $request, Glossary $glossary, GlossarySpelling $spelling): RedirectResponse
    {
        try {
            $spelling->update($request->validated());

            return redirect()->route('glossaries.spellings.show', [$glossary, $spelling])
                ->with('success', 'Spelling updated successfully');
        } catch (\Illuminate\Database\QueryException $e) {
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
