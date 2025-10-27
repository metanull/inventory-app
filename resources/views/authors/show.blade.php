@extends('layouts.app')

@section('content')
    <x-layout.show-page 
        entity="authors"
        :title="$author->name"
        :back-route="route('authors.index')"
        :edit-route="route('authors.edit', $author)"
        :delete-route="route('authors.destroy', $author)"
        delete-confirm="Are you sure you want to delete this author?"
        :backward-compatibility="$author->backward_compatibility"
    >
        @if(session('success'))
            <x-ui.alert :message="session('success')" type="success" entity="authors" />
        @endif

        <x-display.description-list>
            <x-display.field label="Name" :value="$author->name" />
            <x-display.field label="Internal Name" :value="$author->internal_name" />
        </x-display.description-list>

        <x-system-properties 
            :id="$author->id"
            :backward-compatibility-id="$author->backward_compatibility"
            :created-at="$author->created_at"
            :updated-at="$author->updated_at"
        />
    </x-layout.show-page>
@endsection
