<?php

namespace App\Http\Controllers\Web;

use App\Enums\Permission;
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
        $this->middleware('permission:'.Permission::VIEW_DATA->value)->only(['index', 'show']);
        $this->middleware('permission:'.Permission::CREATE_DATA->value)->only(['create', 'store']);
        $this->middleware('permission:'.Permission::UPDATE_DATA->value)->only(['edit', 'update']);
        $this->middleware('permission:'.Permission::DELETE_DATA->value)->only(['destroy']);
    }

    public function index(Request $request): View
    {
        [$projects, $search, $sort, $dir] = $this->searchAndPaginate(
            Project::query(),
            $request,
            ['internal_name', 'launch_date', 'is_launched', 'is_enabled', 'created_at', 'updated_at'],
        );

        return view('projects.index', compact('projects', 'search', 'sort', 'dir'));
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
