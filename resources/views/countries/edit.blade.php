@extends('layouts.app')

@section('content')
    <x-layout.edit-page entity="countries" :model="$country">
        @include('countries._form')
    </x-layout.edit-page>
@endsection
