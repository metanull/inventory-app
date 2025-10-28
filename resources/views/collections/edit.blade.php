@extends('layouts.app')

@section('content')
    <x-layout.edit-page entity="collections" :model="$collection">
        @include('collections._form')
    </x-layout.edit-page>
@endsection
