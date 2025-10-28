@extends('layouts.app')

@section('content')
    <x-layout.create-page entity="partners">
        @include('partners._form', ['partner' => null])
    </x-layout.create-page>
@endsection

