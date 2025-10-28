@extends('layouts.app')

@section('content')
    <x-layout.edit-page entity="languages" :model="$language">
        @include('languages._form')
    </x-layout.edit-page>
@endsection
