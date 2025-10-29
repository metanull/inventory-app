@extends('layouts.app')

@section('content')
    <x-layout.edit-page entity="items" :model="$item">
        @include('items._form')
    </x-layout.edit-page>
@endsection

