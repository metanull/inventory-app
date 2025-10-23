@extends('layouts.app')

@section('content')
    <x-layout.form-page 
        entity="tags"
        title="Edit Tag"
        :back-route="route('tags.show', $tag)"
        :submit-route="route('tags.update', $tag)"
        method="PUT"
    >
        @include('tags._form', ['tag' => $tag])
    </x-layout.form-page>
@endsection
