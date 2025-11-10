@extends('layouts.app')

@section('content')
    <x-layout.show-page-v2
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

        <!-- Main Content Area -->
        <x-display.description-list>
            <x-display.field label="Internal Name" :value="$partner->internal_name" />
            <x-display.field label="Type" :value="$partner->type" />
            <x-display.field label="Country">
                <x-display.country-reference :country="$partner->country" />
            </x-display.field>
            <x-display.field label="Visible" :value="$partner->visible ? 'Yes' : 'No'" />
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
        </x-display.description-list>

        <!-- Images Section -->
        <x-entity.images-section entity="partners" :model="$partner" />

        <!-- Translations Section -->
        <x-entity.translations-section entity="partners" :model="$partner" translationRoute="partner-translations" />

        <!-- Sidebar Content -->
        <x-slot name="sidebar">
            <!-- Monument Item Card -->
            <x-sidebar.monument-item-card :partner="$partner" />

            <!-- System Properties Card -->
            <x-sidebar.system-properties-card
                :id="$partner->id"
                :backward-compatibility-id="$partner->backward_compatibility"
                :created-at="$partner->created_at"
                :updated-at="$partner->updated_at"
            />
        </x-slot>
    </x-layout.show-page-v2>
@endsection
