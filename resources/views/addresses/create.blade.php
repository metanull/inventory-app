@extends('layouts.app')

@section('content')
<x-layout.form-page 
    entity="addresses" 
    title="Create Address" 
    :back-route="route('addresses.index')"
    :submit-route="route('addresses.store')">
    @include('addresses._form', ['address' => null])
</x-layout.form-page>
@endsection
