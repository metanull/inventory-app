@extends('layouts.app')

@section('content')
    <x-layout.create-page entity="countries">
        @include('countries._form', ['country' => null])
    </x-layout.create-page>
@endsection
