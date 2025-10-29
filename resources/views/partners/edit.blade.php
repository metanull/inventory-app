@extends('layouts.app')

@section('content')
    <x-layout.edit-page entity="partners" :model="$partner">
        @include('partners._form')
    </x-layout.edit-page>
@endsection

