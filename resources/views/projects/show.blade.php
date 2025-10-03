@extends('layouts.app')

@section('content')
    <x-layout.show-page 
        entity="projects"
        title="Project Detail"
        :back-route="route('projects.index')"
        :edit-route="route('projects.edit', $project)"
        :delete-route="route('projects.destroy', $project)"
        delete-confirm="Are you sure you want to delete this project?"
        :backward-compatibility="$project->backward_compatibility"
        :badges="array_filter([
            $project->is_launched ? 'Launched' : null,
            $project->is_enabled ? 'Enabled' : null
        ])"
    >
        @if(session('status'))
            <x-ui.alert :message="session('status')" type="success" entity="projects" />
        @endif

        <x-display.description-list>
            <x-display.field label="Internal Name" :value="$project->internal_name" />
            <x-display.field label="Launch Date">
                <x-display.timestamp :datetime="$project->launch_date" />
            </x-display.field>
            <x-display.field label="Launched" :value="$project->is_launched ? 'Yes' : 'No'" />
            <x-display.field label="Enabled" :value="$project->is_enabled ? 'Yes' : 'No'" />
            <x-display.field label="Context">
                <x-display.context-reference :context="$project->context" />
            </x-display.field>
            <x-display.field label="Language">
                <x-display.language-reference :language="$project->language" />
            </x-display.field>
            <x-display.field label="Backward Compatibility" :value="$project->backward_compatibility" />
            <x-display.field label="Created At">
                <x-display.timestamp :datetime="$project->created_at" />
            </x-display.field>
            <x-display.field label="Updated At">
                <x-display.timestamp :datetime="$project->updated_at" />
            </x-display.field>
        </x-display.description-list>
    </x-layout.show-page>
@endsection
