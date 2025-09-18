@extends('layouts.app')

@section('content')
<x-layout.form-page entity="contexts" title="Create Context" :action="route('contexts.store')">
    @include('contexts._form')
</x-layout.form-page>
@endsection
