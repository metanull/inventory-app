@extends('layouts.app')

@section('content')
<x-layout.form-page 
    entity="contacts" 
    title="Edit Contact" 
    :back-route="route('contacts.show', $contact)"
    :submit-route="route('contacts.update', $contact)"
    method="PUT">
    @include('contacts._form', ['contact' => $contact])
</x-layout.form-page>
@endsection
