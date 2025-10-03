@extends('layouts.app')

@section('content')
    <x-layout.show-page 
        entity="languages"
        :title="$language->internal_name"
        :back-route="route('languages.index')"
        :edit-route="route('languages.edit', $language)"
        :delete-route="route('languages.destroy', $language)"
        delete-confirm="Delete this language?"
        :backward-compatibility="$language->backward_compatibility"
        :badges="$language->is_default ? ['Default'] : []"
    >
        @if(session('status'))
            <x-ui.alert :message="session('status')" type="success" entity="languages" />
        @endif

        <x-display.description-list>
            <x-display.field label="ID (ISO 639-3)" :value="$language->id" />
            <x-display.field label="Internal Name" :value="$language->internal_name" />
            <x-display.field label="Backward Compatibility" :value="$language->backward_compatibility" />
            <x-display.field label="Default" :value="$language->is_default ? 'Yes' : 'No'" />
            <x-display.field label="Created At">
                <x-display.timestamp :datetime="$language->created_at" />
            </x-display.field>
            <x-display.field label="Updated At">
                <x-display.timestamp :datetime="$language->updated_at" />
            </x-display.field>
        </x-display.description-list>
    </x-layout.show-page>
@endsection
