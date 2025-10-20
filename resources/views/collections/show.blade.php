@extends('layouts.app')

@section('content')
    <x-layout.show-page 
        entity="collections"
        title="Collection Detail"
        :back-route="route('collections.index')"
        :edit-route="route('collections.edit', $collection)"
        :delete-route="route('collections.destroy', $collection)"
        delete-confirm="Are you sure you want to delete this collection?"
        :backward-compatibility="$collection->backward_compatibility"
    >
        @if(session('status'))
            <x-ui.alert :message="session('status')" type="success" entity="collections" />
        @endif

        <x-display.description-list>
            <x-display.field label="Internal Name" :value="$collection->internal_name" />
            <x-display.field label="Type" :value="ucfirst($collection->type)" />
            <x-display.field label="Language">
                <x-display.language-reference :language="$collection->language" />
            </x-display.field>
            <x-display.field label="Context">
                <x-display.context-reference :context="$collection->context" />
            </x-display.field>
        </x-display.description-list>

        @include('collections._images')

        <!-- Translations Section -->
        @include('collections._translations')

        <!-- System Properties -->
        <x-system-properties 
            :id="$collection->id"
            :backward-compatibility-id="$collection->backward_compatibility"
            :created-at="$collection->created_at"
            :updated-at="$collection->updated_at"
        />
    </x-layout.show-page>
@endsection
