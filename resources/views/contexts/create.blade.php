@extends('layouts.app')

@section('content')
    <x-layout.create-page entity="contexts">
        @include('contexts._form', ['context' => null])
    </x-layout.create-page>
@endsection
