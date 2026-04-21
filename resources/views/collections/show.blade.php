@extends('layouts.app')

@section('content')
    <x-layout.show-page-v2
        entity="collections"
        :title="$collection->internal_name"
        :back-route="route('collections.index')"
        :edit-route="route('collections.edit', $collection)"
        :delete-route="route('collections.destroy', $collection)"
        delete-confirm="Are you sure you want to delete this collection?"
        :backward-compatibility="$collection->backward_compatibility"
        :breadcrumbs="$breadcrumbs"
    >
        <x-display.description-list>
            <x-display.field label="Internal Name" :value="$collection->internal_name" />
            <x-display.field label="Type" :value="ucfirst($collection->type)" />
            <x-display.field label="Language">
                <x-display.language-reference :language="$collection->language" />
            </x-display.field>
            <x-display.field label="Context">
                <x-display.context-reference :context="$collection->context" />
            </x-display.field>
            <x-display.field label="Display Order" :value="$collection->display_order" />
        </x-display.description-list>

        <x-entity.images-section entity="collections" :model="$collection" :images="$collectionImages" />

        <!-- Child Collections Section -->
        <x-entity.children-section :model="$collection" :children="$childCollections" />

        <!-- Items Section -->
        <x-entity.collection-items-section :model="$collection" :items="$collection->attachedItems" :attachable-items="$attachableItems" />

        <!-- Translations Section -->
        <x-entity.translations-section entity="collections" :model="$collection" translationRoute="collection-translations" :translation-groups="$translationGroups" />

        <!-- Sidebar Content -->
        <x-slot name="sidebar">
            <!-- Parent Collection Card -->
            <x-sidebar.parent-collection-card :model="$collection" :parent-collection="$collection->parent" :parent-options="$parentOptions" />

            <!-- Children Collections Card -->
            <x-sidebar.children-collections-card :model="$collection" :children="$childCollections" />

            <!-- System Properties Card -->
            <x-sidebar.system-properties-card
                :id="$collection->id"
                :backward-compatibility-id="$collection->backward_compatibility"
                :created-at="$collection->created_at"
                :updated-at="$collection->updated_at"
            />
        </x-slot>
    </x-layout.show-page-v2>
@endsection
