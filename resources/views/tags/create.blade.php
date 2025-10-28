@extends('layouts.app')

@section('content')
    <x-layout.create-page entity="tags">
        @include('tags._form', ['tag' => null])
    </x-layout.create-page>
@endsection
