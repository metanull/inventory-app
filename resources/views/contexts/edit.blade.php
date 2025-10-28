@extends('layouts.app')

@section('content')
    <x-layout.edit-page entity="contexts" :model="$context">
        @include('contexts._form')
    </x-layout.edit-page>
@endsection
