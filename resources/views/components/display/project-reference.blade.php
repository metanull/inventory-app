@props([
    'project' => null,
])

@if($project)
    {{ $project->internal_name }}
@else
    —
@endif