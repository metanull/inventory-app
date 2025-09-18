@extends('layouts.app')

@section('content')
<x-layout.form-page entity="collections" title="Edit Collection" :action="route('collections.update', $collection)" method="PUT">
    @include('collections._form')
</x-layout.form-page>
@endsection
