@extends('layouts.app')

@section('content')
<x-layout.form-page 
    entity="authors" 
    title="Edit Author" 
    :back-route="route('authors.show', $author)"
    :submit-route="route('authors.update', $author)"
    method="PUT">
    @include('authors._form', ['author' => $author])
</x-layout.form-page>
@endsection
