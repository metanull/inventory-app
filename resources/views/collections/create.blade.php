@extends('layouts.app')

@section('content')
<x-layout.form-page entity="collections" title="Create Collection" :action="route('collections.store')">
    @include('collections._form')
</x-layout.form-page>
@endsection
