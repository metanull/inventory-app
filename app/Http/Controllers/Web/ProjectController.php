<?php

namespace App\Http\Controllers\Web;

use App\Enums\Permission;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\IndexProjectRequest;
use App\Http\Requests\Web\StoreProjectRequest;
use App\Http\Requests\Web\UpdateProjectRequest;
use App\Models\Project;
use App\Services\Web\ProjectIndexQuery;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ProjectController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:'.Permission::VIEW_DATA->value)->only(['index', 'show']);
        $this->middleware('permission:'.Permission::CREATE_DATA->value)->only(['create', 'store']);
        $this->middleware('permission:'.Permission::UPDATE_DATA->value)->only(['edit', 'update']);
        $this->middleware('permission:'.Permission::DELETE_DATA->value)->only(['destroy']);
    }

    public function index(IndexProjectRequest $request, ProjectIndexQuery $projectIndexQuery): View
    {
        $listState = $request->listState();

        return view('projects.index', [
            'projects' => $projectIndexQuery->paginate($listState),
            'listState' => $listState,
        ]);
    }

    public function show(Project $project): View
    {
        $project->load(['context', 'language']);

        return view('projects.show', compact('project'));
    }

    public function create(): View
    {
        return view('projects.create');
    }

    public function store(StoreProjectRequest $request): RedirectResponse
    {
        $project = Project::create($request->validated());

        return redirect()->route('projects.show', $project)->with('success', 'Project created successfully');
    }

    public function edit(Project $project): View
    {
        $project->load(['context', 'language']);

        return view('projects.edit', compact('project'));
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
