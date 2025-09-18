<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\StoreProjectRequest;
use App\Http\Requests\Web\UpdateProjectRequest;
use App\Models\Context;
use App\Models\Language;
use App\Models\Project;
use App\Support\Web\SearchAndPaginate;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    use SearchAndPaginate;

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request): View
    {
        [$projects, $search] = $this->searchAndPaginate(Project::query(), $request);

        return view('projects.index', compact('projects', 'search'));
    }

    public function show(Project $project): View
    {
        $project->load(['context', 'language']);

        return view('projects.show', compact('project'));
    }

    public function create(): View
    {
        $contexts = Context::query()->orderBy('internal_name')->get(['id', 'internal_name']);
        $languages = Language::query()->orderBy('id')->get(['id', 'internal_name']);

        return view('projects.create', compact('contexts', 'languages'));
    }

    public function store(StoreProjectRequest $request): RedirectResponse
    {
        $project = Project::create($request->validated());

        return redirect()->route('projects.show', $project)->with('success', 'Project created successfully');
    }

    public function edit(Project $project): View
    {
        $project->load(['context', 'language']);
        $contexts = Context::query()->orderBy('internal_name')->get(['id', 'internal_name']);
        $languages = Language::query()->orderBy('id')->get(['id', 'internal_name']);

        return view('projects.edit', compact('project', 'contexts', 'languages'));
    }

    public function update(UpdateProjectRequest $request, Project $project): RedirectResponse
    {
        $project->update($request->validated());

        return redirect()->route('projects.show', $project)->with('success', 'Project updated successfully');
    }

    public function destroy(Project $project): RedirectResponse
    {
        $project->delete();

        return redirect()->route('projects.index')->with('success', 'Project deleted successfully');
    }
}
