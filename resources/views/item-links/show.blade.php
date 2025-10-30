@extends('layouts.app')

@section('content')
    <x-layout.show-page 
        entity="item-item-links"
        :title="'Link: ' . $itemItemLink->target->internal_name"
        :back-route="route('item-links.index', $item)"
        :edit-route="route('item-links.edit', [$item, $itemItemLink])"
        :delete-route="route('item-links.destroy', [$item, $itemItemLink])"
        delete-confirm="Are you sure you want to delete this link?"
    >
        @if(session('success'))
            <x-ui.alert :message="session('success')" type="success" entity="item-item-links" />
        @endif

        <x-display.description-list>
            <x-display.field label="Source Item">
                <x-display.item-reference :item="$itemItemLink->source" />
            </x-display.field>
            <x-display.field label="Target Item">
                <x-display.item-reference :item="$itemItemLink->target" />
            </x-display.field>
            <x-display.field label="Context">
                <x-display.context-reference :context="$itemItemLink->context" />
            </x-display.field>
        </x-display.description-list>

        <!-- System Properties -->
        <x-system-properties 
            :id="$itemItemLink->id"
            :created-at="$itemItemLink->created_at"
            :updated-at="$itemItemLink->updated_at"
        />
    </x-layout.show-page>
@endsection
