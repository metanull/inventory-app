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

        <x-entity.images-section entity="collections" :model="$collection" :images="$sections['images']['images']" />

        <!-- Child Collections Section -->
        <x-entity.children-section :model="$collection" :children="$sections['children']['items']" />

        <!-- Items Section -->
        <x-entity.collection-items-section :model="$collection" :items="$sections['items']['items']" :attachable-items="$sections['items']['attachableItems']" />

        <!-- Translations Section -->
        <x-entity.translations-section entity="collections" :model="$collection" translationRoute="collection-translations" :translation-groups="$sections['translations']['groups']" />

        <!-- Sidebar Content -->
        <x-slot name="sidebar">
            <!-- Parent Collection Card -->
            <x-sidebar.parent-collection-card :model="$collection" :parent-collection="$sections['parent']['collection']" :parent-options="$sections['parent']['options']" />

            <!-- Children Collections Card -->
            <x-sidebar.children-collections-card :model="$collection" :children="$sections['children']['items']" />

            <!-- System Properties Card -->
            <x-sidebar.system-properties-card
                :id="$sections['system']['id']"
                :backward-compatibility-id="$sections['system']['backwardCompatibilityId']"
                :created-at="$sections['system']['createdAt']"
                :updated-at="$sections['system']['updatedAt']"
            />
        </x-slot>
    </x-layout.show-page-v2>
@endsection
