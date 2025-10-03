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
            <x-display.field label="Backward Compatibility" :value="$context->backward_compatibility" />
            <x-display.field label="Created At">
                <x-display.timestamp :datetime="$context->created_at" />
            </x-display.field>
            <x-display.field label="Updated At">
                <x-display.timestamp :datetime="$context->updated_at" />
            </x-display.field>
        </x-display.description-list>
    </x-layout.show-page>
@endsection
