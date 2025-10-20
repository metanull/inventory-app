@extends('layouts.app')

@section('content')
    <x-layout.show-page 
        entity="contexts"
        title="Context Detail"
        :back-route="route('contexts.index')"
        :edit-route="route('contexts.edit', $context)"
        :delete-route="route('contexts.destroy', $context)"
        delete-confirm="Are you sure you want to delete this context?"
        :backward-compatibility="$context->backward_compatibility"
        :badges="$context->is_default ? ['Default'] : []"
    >
        @if(session('status'))
            <x-ui.alert :message="session('status')" type="success" entity="contexts" />
        @endif

        <x-display.description-list>
            <x-display.field label="Internal Name" :value="$context->internal_name" />
            <x-display.field label="Default" :value="$context->is_default ? 'Yes' : 'No'" />
        </x-display.description-list>

        <!-- System Properties -->
        <x-system-properties 
            :id="$context->id"
            :backward-compatibility-id="$context->backward_compatibility"
            :created-at="$context->created_at"
            :updated-at="$context->updated_at"
        />
    </x-layout.show-page>
@endsection
