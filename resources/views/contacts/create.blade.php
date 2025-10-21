@extends('layouts.app')

@section('content')
<x-layout.form-page 
    entity="contacts" 
    title="Create Contact" 
    :back-route="route('contacts.index')"
    :submit-route="route('contacts.store')">
    @include('contacts._form', ['contact' => null])
</x-layout.form-page>
@endsection
