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
            <x-display.field label="Backward Compatibility" :value="$partner->backward_compatibility" />
            <x-display.field label="Created At">
                <x-display.timestamp :datetime="$partner->created_at" />
            </x-display.field>
            <x-display.field label="Updated At">
                <x-display.timestamp :datetime="$partner->updated_at" />
            </x-display.field>
        </x-display.description-list>
    </x-layout.show-page>
@endsection
