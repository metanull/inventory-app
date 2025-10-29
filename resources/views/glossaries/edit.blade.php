@extends('layouts.app')

@section('content')
    <x-layout.form-page 
        entity="glossary"
        title="Edit Glossary Entry"
        :back-route="route('glossaries.show', $glossary)"
        :submit-route="route('glossaries.update', $glossary)"
        method="PUT"
    >
        @include('glossaries._form')
    </x-layout.form-page>
@endsection


