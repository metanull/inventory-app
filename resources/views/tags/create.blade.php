@extends('layouts.app')

@section('content')
    <x-layout.form-page 
        entity="tags"
        title="Create Tag"
        :back-route="route('tags.index')"
        :submit-route="route('tags.store')"
    >
        @include('tags._form', ['tag' => null])
    </x-layout.form-page>
@endsection
