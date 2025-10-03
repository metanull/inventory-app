@extends('layouts.app')

@section('content')
<x-layout.form-page 
    entity="contexts" 
    title="Create Context" 
    :back-route="route('contexts.index')"
    :submit-route="route('contexts.store')">
    @include('contexts._form', ['context' => null])
</x-layout.form-page>
@endsection
