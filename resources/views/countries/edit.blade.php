@extends('layouts.app')

@section('content')
    <x-layout.form-page 
        entity="countries"
        title="Edit Country"
        :back-route="route('countries.show', $country)"
        :submit-route="route('countries.update', $country)"
        method="PUT"
    >
        @include('countries._form', ['country' => $country])
    </x-layout.form-page>
@endsection
