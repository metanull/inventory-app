@extends('layouts.app')

@section('content')
<x-layout.show-page entity="projects" :title="$project->internal_name" :model="$project" :backward-compatibility="$project->backward_compatibility">
    <x-display.description-list title="Information">
        <x-display.field label="Internal Name" :value="$project->internal_name" variant="gray" />
        <x-display.date label="Launch Date" :value="$project->launch_date" />
        <x-display.boolean label="Launched" :value="$project->is_launched" variant="gray" />
        <x-display.boolean label="Enabled" :value="$project->is_enabled" />
        <x-display.context-reference label="Context" :value="$project->context" variant="gray" />
        <x-display.language-reference label="Language" :value="$project->language" />
        <x-display.field label="Legacy ID" :value="$project->backward_compatibility" variant="gray" />
    </x-display.description-list>
</x-layout.show-page>
@endsection
