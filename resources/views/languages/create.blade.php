@extends('layouts.app')

@section('content')
    <x-layout.create-page entity="languages">
        @include('languages._form', ['language' => null])
    </x-layout.create-page>
@endsection
