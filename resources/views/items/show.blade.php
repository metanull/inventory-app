@extends('layouts.app')

@section('content')
    <x-layout.show-page 
        entity="items"
        :title="$item->internal_name"
        :back-route="route('items.index')"
        :edit-route="route('items.edit', $item)"
        :delete-route="route('items.destroy', $item)"
        delete-confirm="Are you sure you want to delete this item?"
        :backward-compatibility="$item->backward_compatibility"
    >
        @if(session('status'))
            <x-ui.alert :message="session('status')" type="success" entity="items" />
        @endif

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
            <x-display.field label="Backward Compatibility" :value="$item->backward_compatibility" />
            <x-display.field label="Created At">
                <x-display.timestamp :datetime="$item->created_at" />
            </x-display.field>
            <x-display.field label="Updated At">
                <x-display.timestamp :datetime="$item->updated_at" />
            </x-display.field>
        </x-display.description-list>
    </x-layout.show-page>
@endsection
