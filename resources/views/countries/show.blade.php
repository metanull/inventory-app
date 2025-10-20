@extends('layouts.app')

@section('content')
    <x-layout.show-page 
        entity="countries"
        :title="$country->internal_name"
        :back-route="route('countries.index')"
        :edit-route="route('countries.edit', $country)"
        :delete-route="route('countries.destroy', $country)"
        delete-confirm="Delete this country?"
        :backward-compatibility="$country->backward_compatibility"
    >
        @if(session('status'))
            <x-ui.alert :message="session('status')" type="success" entity="countries" />
        @endif

        <x-display.description-list>
            <x-display.field label="ID (ISO 3166-1 alpha-3)" :value="$country->id" />
            <x-display.field label="Internal Name" :value="$country->internal_name" />
        </x-display.description-list>

        <!-- System Properties -->
        <x-system-properties 
            :id="$country->id"
            :backward-compatibility-id="$country->backward_compatibility"
            :created-at="$country->created_at"
            :updated-at="$country->updated_at"
        />
    </x-layout.show-page>
@endsection
