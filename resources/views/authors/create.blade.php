@extends('layouts.app')

@section('content')
    <x-layout.create-page entity="authors">
        @include('authors._form', ['author' => null])
    </x-layout.create-page>
@endsection
