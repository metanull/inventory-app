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
            <x-display.field label="Default" :value="$language->is_default ? 'Yes' : 'No'" />
        </x-display.description-list>

        <!-- System Properties -->
        <x-system-properties 
            :id="$language->id"
            :backward-compatibility-id="$language->backward_compatibility"
            :created-at="$language->created_at"
            :updated-at="$language->updated_at"
        />
    </x-layout.show-page>
@endsection
