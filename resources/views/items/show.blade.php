@extends('layouts.app')

@section('content')
    <x-layout.show-page-v2
        entity="items"
        :title="$item->internal_name"
        :back-route="route('items.index')"
        :edit-route="route('items.edit', $item)"
        :delete-route="route('items.destroy', $item)"
        delete-confirm="Are you sure you want to delete this item?"
        :backward-compatibility="$item->backward_compatibility"
    >
        <!-- Main Content Area -->
        <x-display.description-list>
            <x-display.field label="Internal Name" :value="$item->internal_name" />
            <x-display.field label="Type" :value="$item->type === 'object' ? 'Object' : 'Monument'" />
            <x-display.field label="Country">
                <x-display.country-reference :country="$item->country" />
            </x-display.field>
            <x-display.field label="Partner">
                <x-display.partner-reference :partner="$item->partner" />
            </x-display.field>
            <x-display.field label="Project">
                <x-display.project-reference :project="$item->project" />
            </x-display.field>
        </x-display.description-list>

        <!-- Images Section -->
        <x-entity.images-section entity="items" :model="$item" />

        <!-- Translations Section -->
        <x-entity.translations-section entity="items" :model="$item" translationRoute="item-translations" />

        <!-- Sidebar Content -->
        <x-slot name="sidebar">
            <!-- Parent Item Card -->
            <x-sidebar.parent-item-card :model="$item" />

            <!-- Children Items Card -->
            <x-sidebar.children-items-card :model="$item" />

            <!-- Tags Card -->
            <x-sidebar.tags-card :model="$item" />

            <!-- Links Card -->
            <x-sidebar.links-card :model="$item" />

            <!-- System Properties Card -->
            <x-sidebar.system-properties-card
                :id="$item->id"
                :backward-compatibility-id="$item->backward_compatibility"
                :created-at="$item->created_at"
                :updated-at="$item->updated_at"
            />
        </x-slot>
    </x-layout.show-page-v2>
@endsection
