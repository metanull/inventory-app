@extends('layouts.app')

@section('content')
<x-layout.form-page 
    entity="addresses" 
    title="Edit Address" 
    :back-route="route('addresses.show', $address)"
    :submit-route="route('addresses.update', $address)"
    method="PUT">
    @include('addresses._form', ['address' => $address])
</x-layout.form-page>
@endsection
