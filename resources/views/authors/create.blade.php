@extends('layouts.app')

@section('content')
<x-layout.form-page 
    entity="authors" 
    title="Create Author" 
    :back-route="route('authors.index')"
    :submit-route="route('authors.store')">
    @include('authors._form', ['author' => null])
</x-layout.form-page>
@endsection
