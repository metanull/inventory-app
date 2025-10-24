@extends('layouts.app')

@section('content')
    <x-layout.show-page 
        entity="partners"
        title="Partner Detail"
        :back-route="route('partners.index')"
        :edit-route="route('partners.edit', $partner)"
        :delete-route="route('partners.destroy', $partner)"
        delete-confirm="Are you sure you want to delete this partner?"
        :backward-compatibility="$partner->backward_compatibility"
    >
        @if(session('status'))
            <x-ui.alert :message="session('status')" type="success" entity="partners" />
        @endif

        <x-display.description-list>
            <x-display.field label="Internal Name" :value="$partner->internal_name" />
            <x-display.field label="Type" :value="$partner->type" />
            <x-display.field label="Country">
                <x-display.country-reference :country="$partner->country" />
            </x-display.field>
            <x-display.field label="Visible">
                <x-display.boolean :value="$partner->visible" />
            </x-display.field>
            <x-display.field label="GPS Location">
                <x-display.gps-location 
                    :latitude="$partner->latitude" 
                    :longitude="$partner->longitude"
                    :map-zoom="$partner->map_zoom"
                />
            </x-display.field>
            <x-display.field label="Project">
                <x-display.project-reference :project="$partner->project" />
            </x-display.field>
            <x-display.field label="Monument Item">
                <x-display.item-reference :item="$partner->monumentItem" />
            </x-display.field>
        </x-display.description-list>

        <!-- System Properties -->
        <x-system-properties 
            :id="$partner->id"
            :backward-compatibility-id="$partner->backward_compatibility"
            :created-at="$partner->created_at"
            :updated-at="$partner->updated_at"
        />
    </x-layout.show-page>
@endsection
