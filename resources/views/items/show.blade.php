@extends('layouts.app')

@section('content')
    <x-layout.show-page-v2
        entity="items"
        :title="$item->internal_name"
        :back-route="isset($collection) ? route('collections.show', $collection) : route('items.index')"
        :edit-route="route('items.edit', $item)"
        :delete-route="route('items.destroy', $item)"
        delete-confirm="Are you sure you want to delete this item?"
        :backward-compatibility="$item->backward_compatibility"
        :breadcrumbs="$breadcrumbs"
    >
        <!-- Main Content Area -->
        <x-display.description-list>
            <x-display.field label="Internal Name" :value="$item->internal_name" />
            <x-display.field label="Type" :value="$item->type?->label() ?? '—'" />
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
        <x-entity.images-section entity="items" :model="$item" :images="$sections['images']['images']" />

        @if($sections['pictureChildren']['items']->isNotEmpty())
            <x-entity.picture-children-section
                :items="$sections['pictureChildren']['items']"
                :collection="$collection ?? null"
            />
        @endif

        <!-- Translations Section -->
        <x-entity.translations-section entity="items" :model="$item" translationRoute="item-translations" :translation-groups="$sections['translations']['groups']" />

        <!-- Sidebar Content -->
        <x-slot name="sidebar">
            <!-- Parent Item Card -->
            <x-sidebar.parent-item-card :model="$item" :parent-item="$sections['parent']['item']" :collection="$collection ?? null" />

            <!-- Children Items Card -->
            <x-sidebar.children-items-card :model="$item" :children="$sections['children']['items']" :collection="$collection ?? null" />

            <!-- Tags Card -->
            <x-sidebar.tags-card :model="$item" :tags="$sections['tags']['items']" />

            <!-- Links Card -->
            <x-sidebar.links-card :model="$item" :formatted-links="$sections['links']['formatted']" :collection="$collection ?? null" />

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
