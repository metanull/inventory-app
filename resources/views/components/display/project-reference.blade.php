@props(['project' => null, 'link' => true])

@php($c = $entityColor('projects'))

@if($project)
    @if($link)
        <a 
            href="{{ route('projects.show', $project) }}" 
            class="{{ $c['accentLink'] }}"
        >
            {{ $project->internal_name }}
        </a>
    @else
        {{ $project->internal_name }}
    @endif
@else
    <span class="text-gray-400">N/A</span>
@endif