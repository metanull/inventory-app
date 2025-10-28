@extends('layouts.app')

@section('content')
    <x-layout.create-page entity="items">
        @include('items._form', ['item' => null])
    </x-layout.create-page>
@endsection

