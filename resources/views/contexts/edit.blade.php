@extends('layouts.app')

@section('content')
<x-layout.form-page 
    entity="contexts" 
    title="Edit Context" 
    :back-route="route('contexts.show', $context)"
    :submit-route="route('contexts.update', $context)" 
    method="PUT">
    @include('contexts._form')
</x-layout.form-page>
@endsection
