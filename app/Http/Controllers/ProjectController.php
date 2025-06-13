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
            'id' => 'required|uuid',
            'internal_name' => 'required',
            'backward_compatibility' => 'nullable|string',
            'launch_date' => 'nullable|date',
            'is_launched' => 'boolean',
            'is_enabled' => 'boolean',
            'context_id' => 'nullable|uuid',
            'language_id' => 'nullable|string|size:3',
        ]);
        $project = Project::create($validated);

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
            'id' => 'prohibited|uuid',
            'internal_name' => 'required',
            'backward_compatibility' => 'nullable|string',
            'launch_date' => 'nullable|date',
            'is_launched' => 'boolean',
            'is_enabled' => 'boolean',
            'context_id' => 'nullable|uuid',
            'language_id' => 'nullable|string|size:3',
        ]);
        $project->update($validated);

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
}
