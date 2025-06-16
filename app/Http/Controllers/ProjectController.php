<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProjectResource;
use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return ProjectResource::collection(Project::all());
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
        $project->load(['context', 'language']);

        return new ProjectResource($project);
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project)
    {
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
            'internal_name' => 'string',
            'backward_compatibility' => 'nullable|string',
            'launch_date' => 'nullable|date',
            'is_launched' => 'boolean',
            'is_enabled' => 'boolean',
            'context_id' => 'nullable|uuid',
            'language_id' => 'nullable|string|size:3',
        ]);
        $project->update($validated);
        $project->refresh();
        $project->load(['context', 'language']);

        return new ProjectResource($project);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project)
    {
        $project->delete();

        return response()->json(null, 204);
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
     */
    public function setLaunched(Request $request, Project $project)
    {
        $validated = $request->validate([
            'launch_date' => 'nullable|date',
        ]);
        if ($validated['launch_date'] === null) {
            $validated['is_launched'] = false;
        } else {
            $validated['is_launched'] = true;
        }
        $project->update($validated);
        $project->refresh();

        return new ProjectResource($project);
    }
}
