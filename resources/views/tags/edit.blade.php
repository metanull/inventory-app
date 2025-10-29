@extends('layouts.app')

@section('content')
    <x-layout.edit-page entity="tags" :model="$tag">
        @include('tags._form')
    </x-layout.edit-page>
@endsection
