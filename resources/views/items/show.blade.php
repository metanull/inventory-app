@extends('layouts.app')

@section('content')
    <x-layout.show-page-v2
        entity="items"
        :title="$item->internal_name"
        :back-route="isset($collection) ? route('collections.index') : route('items.index')"
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
        <x-entity.images-section entity="items" :model="$item" :images="$itemImages" />

        <!-- Translations Section -->
        <x-entity.translations-section entity="items" :model="$item" translationRoute="item-translations" :translation-groups="$translationGroups" />

        <!-- Sidebar Content -->
        <x-slot name="sidebar">
            <!-- Parent Item Card -->
            <x-sidebar.parent-item-card :model="$item" :parent-item="$item->parent" :parent-options="$parentOptions" :collection="$collection ?? null" />

            <!-- Children Items Card -->
            <x-sidebar.children-items-card :model="$item" :children="$item->children" :child-options="$childOptions" :collection="$collection ?? null" />

            <!-- Tags Card -->
            <x-sidebar.tags-card :model="$item" :tags="$item->tags" :available-tags="$availableTags" />

            <!-- Links Card -->
            <x-sidebar.links-card :model="$item" :formatted-links="$formattedLinks" :link-target-options="$linkTargetOptions" :context-options="$contextOptions" :collection="$collection ?? null" />

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
