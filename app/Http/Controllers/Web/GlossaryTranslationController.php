<?php

namespace App\Http\Controllers\Web;

use App\Enums\Permission;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\IndexGlossaryTranslationRequest;
use App\Http\Requests\Web\StoreGlossaryTranslationRequest;
use App\Http\Requests\Web\UpdateGlossaryTranslationRequest;
use App\Models\Glossary;
use App\Models\GlossaryTranslation;
use App\Models\Language;
use App\Services\Web\GlossaryTranslationIndexQuery;
use App\Support\Web\Lists\ListState;
use Illuminate\Contracts\View\View;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;

class GlossaryTranslationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:'.Permission::VIEW_DATA->value)->only(['index', 'show']);
        $this->middleware('permission:'.Permission::CREATE_DATA->value)->only(['create', 'store']);
        $this->middleware('permission:'.Permission::UPDATE_DATA->value)->only(['edit', 'update']);
        $this->middleware('permission:'.Permission::DELETE_DATA->value)->only(['destroy']);
    }

    public function index(Glossary $glossary, IndexGlossaryTranslationRequest $request, GlossaryTranslationIndexQuery $glossaryTranslationIndexQuery): View
    {
        $listState = $request->listState();

        return view('glossary-translation.index', [
            'translations' => $glossaryTranslationIndexQuery->paginate($listState),
            'listState' => $listState,
            'glossary' => $glossary,
            'selectedLanguage' => $this->resolveSelectedLanguage($listState),
        ]);
    }

    public function create(Glossary $glossary): View
    {
        $usedLanguageIds = $glossary->translations()->pluck('language_id')->toArray();
        $languageOptions = Language::orderBy('internal_name')->get()->map(function ($lang) use ($usedLanguageIds) {
            $lang->disabled = in_array($lang->id, $usedLanguageIds);

            return $lang;
        });

        return view('glossary-translation.create', compact('glossary', 'languageOptions'));
    }

    public function store(StoreGlossaryTranslationRequest $request, Glossary $glossary): RedirectResponse
    {
        $data = $request->validated();
        $data['glossary_id'] = $glossary->id;

        try {
            $translation = GlossaryTranslation::create($data);

            return redirect()->route('glossaries.translations.show', [$glossary, $translation])
                ->with('success', 'Translation created successfully');
        } catch (QueryException $e) {
            // Check if it's a unique constraint violation
            if ($e->getCode() === '23000' || str_contains($e->getMessage(), 'Duplicate entry') || str_contains($e->getMessage(), 'UNIQUE constraint')) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['language_id' => 'A translation for this language already exists for this glossary entry.']);
            }

            throw $e;
        }
    }

    public function show(Glossary $glossary, GlossaryTranslation $translation): View
    {
        $translation->load('language');

        return view('glossary-translation.show', compact('glossary', 'translation'));
    }

    public function edit(Glossary $glossary, GlossaryTranslation $translation): View
    {
        $translation->load('language');
        $usedLanguageIds = $glossary->translations()
            ->where('id', '!=', $translation->id)
            ->pluck('language_id')
            ->toArray();
        $languageOptions = Language::orderBy('internal_name')->get()->map(function ($lang) use ($usedLanguageIds) {
            $lang->disabled = in_array($lang->id, $usedLanguageIds);

            return $lang;
        });

        return view('glossary-translation.edit', compact('glossary', 'translation', 'languageOptions'));
    }

    public function update(UpdateGlossaryTranslationRequest $request, Glossary $glossary, GlossaryTranslation $translation): RedirectResponse
    {
        try {
            $translation->update($request->validated());

            return redirect()->route('glossaries.translations.show', [$glossary, $translation])
                ->with('success', 'Translation updated successfully');
        } catch (QueryException $e) {
            // Check if it's a unique constraint violation
            if ($e->getCode() === '23000' || str_contains($e->getMessage(), 'Duplicate entry') || str_contains($e->getMessage(), 'UNIQUE constraint')) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['language_id' => 'A translation for this language already exists for this glossary entry.']);
            }

            throw $e;
        }
    }

    public function destroy(Glossary $glossary, GlossaryTranslation $translation): RedirectResponse
    {
        $translation->delete();

        return redirect()->route('glossaries.translations.index', $glossary)
            ->with('success', 'Translation deleted successfully');
    }

    private function resolveSelectedLanguage(ListState $listState): ?Language
    {
        $languageId = $listState->filters['language'] ?? null;

        if (! is_string($languageId) || $languageId === '') {
            return null;
        }

        return Language::query()->select('id', 'internal_name')->find($languageId);
    }
}
