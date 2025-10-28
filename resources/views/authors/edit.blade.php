@extends('layouts.app')

@section('content')
    <x-layout.edit-page entity="authors" :model="$author">
        @include('authors._form')
    </x-layout.edit-page>
@endsection
