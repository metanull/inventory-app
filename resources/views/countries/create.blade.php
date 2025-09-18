@extends('layouts.app')

@section('content')
    <x-layout.form-page 
        entity="countries"
        title="Create Country"
        :back-route="route('countries.index')"
        :submit-route="route('countries.store')"
    >
        @include('countries._form', ['country' => null])
    </x-layout.form-page>
@endsection
