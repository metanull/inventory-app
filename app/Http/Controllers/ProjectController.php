<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\IndexProjectRequest;
use App\Http\Requests\Api\ShowProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use App\Support\Includes\AllowList;
use App\Support\Includes\IncludeParser;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexProjectRequest $request)
    {
        $includes = $request->getIncludeParams();
        $pagination = $request->getPaginationParams();

        $query = Project::query();
        if (! empty($includes)) {
            $query->with($includes);
        }

        $paginator = $query->paginate($pagination['per_page'], ['*'], 'page', $pagination['page']);

        return ProjectResource::collection($paginator);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            /** @ignoreParam */
            'id' => 'prohibited',
            'internal_name' => 'required|string',
            'backward_compatibility' => 'nullable|string',
            'launch_date' => 'nullable|date',
            'is_launched' => 'boolean',
            'is_enabled' => 'boolean',
            'context_id' => 'nullable|uuid',
            'language_id' => 'nullable|string|size:3',
        ]);
        $project = Project::create($validated);
        $project->refresh();
        // By default include context and language for store response; also honor requested includes
        $requested = IncludeParser::fromRequest($request, AllowList::for('project'));
        $project->load(array_values(array_unique(array_merge(['context', 'language'], $requested))));

        return new ProjectResource($project);
    }

    /**
     * Display the specified resource.
     */
    public function show(ShowProjectRequest $request, Project $project)
    {
        $includes = $request->getIncludeParams();
        if (! empty($includes)) {
            $project->load($includes);
        }

        return new ProjectResource($project);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            /** @ignoreParam */
            'id' => 'prohibited',
            'internal_name' => 'required|string',
            'backward_compatibility' => 'nullable|string',
            'launch_date' => 'nullable|date',
            'is_launched' => 'boolean',
            'is_enabled' => 'boolean',
            'context_id' => 'nullable|uuid',
            'language_id' => 'nullable|string|size:3',
        ]);
        $project->update($validated);
        $project->refresh();
        // By default include context and language for update response; also honor requested includes
        $requested = IncludeParser::fromRequest($request, AllowList::for('project'));
        $project->load(array_values(array_unique(array_merge(['context', 'language'], $requested))));

        return new ProjectResource($project);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project)
    {
        $project->delete();

        return response()->noContent();
    }

    /**
     * Toggle Enable/disable on a project.
     */
    public function setEnabled(Request $request, Project $project)
    {
        $validated = $request->validate([
            'is_enabled' => 'required|boolean',
        ]);

        $project->update($validated);
        $project->refresh();

        return new ProjectResource($project);
    }

    /**
     * Toggle Launched/not-launched on a project.
     * Important: It is independant from the `launch_date` value. It is an idicator showing that
     * the project is to be considered 'laucnhed' as soon as the launch date it reached.
     */
    public function setLaunched(Request $request, Project $project)
    {
        $validated = $request->validate([
            'is_launched' => 'required|boolean',
        ]);

        $project->update($validated);
        $project->refresh();

        return new ProjectResource($project);
    }

    /**
     * Get all visible projects.
     * The project becomes "visible" when all conditions are matched:
     * - is_enabled is true
     * - is_launched is true
     * - current date >= launch_date
     */
    public function enabled(Request $request)
    {
        $includes = IncludeParser::fromRequest($request, AllowList::for('project'));
        $query = Project::visible();
        if (! empty($includes)) {
            $query->with($includes);
        }

        return ProjectResource::collection($query->get());
    }
}
