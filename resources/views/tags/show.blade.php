@extends('layouts.app')

@section('content')
    <x-layout.show-page 
        entity="tags"
        :title="$tag->internal_name"
        :back-route="route('tags.index')"
        :edit-route="route('tags.edit', $tag)"
        :delete-route="route('tags.destroy', $tag)"
        delete-confirm="Are you sure you want to delete this tag?"
        :backward-compatibility="$tag->backward_compatibility"
    >
        @if(session('status'))
            <x-ui.alert :message="session('status')" type="success" entity="tags" />
        @endif

        <x-display.description-list>
            <x-display.field label="Internal Name" :value="$tag->internal_name" />
            <x-display.field label="Description" :value="$tag->description" />
        </x-display.description-list>

        <!-- System Properties -->
        <x-system-properties 
            :id="$tag->id"
            :backward-compatibility-id="$tag->backward_compatibility"
            :created-at="$tag->created_at"
            :updated-at="$tag->updated_at"
        />
    </x-layout.show-page>
@endsection
