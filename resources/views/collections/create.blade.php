@extends('layouts.app')

@section('content')
<x-layout.form-page 
    entity="collections" 
    title="Create Collection" 
    :back-route="route('collections.index')"
    :submit-route="route('collections.store')">
    @include('collections._form', ['collection' => null])
</x-layout.form-page>
@endsection
