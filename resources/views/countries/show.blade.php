@extends('layouts.app')

@section('content')
    <x-layout.show-page 
        entity="countries"
        :title="$country->internal_name"
        :back-route="route('countries.index')"
        :edit-route="route('countries.edit', $country)"
        :delete-route="route('countries.destroy', $country)"
        delete-confirm="Delete this country?"
        :backward-compatibility="$country->backward_compatibility"
    >
        <x-display.description-list>
            <x-display.field label="ID (ISO 3166-1 alpha-3)" :value="$country->id" />
            <x-display.field label="Internal Name" :value="$country->internal_name" />
            <x-display.field label="Backward Compatibility" :value="$country->backward_compatibility" />
            <x-display.field label="Created At">
                <x-display.timestamp :datetime="$country->created_at" />
            </x-display.field>
            <x-display.field label="Updated At">
                <x-display.timestamp :datetime="$country->updated_at" />
            </x-display.field>
        </x-display.description-list>
    </x-layout.show-page>
@endsection
