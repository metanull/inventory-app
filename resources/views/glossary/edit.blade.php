@extends('layouts.app')

@section('content')
<x-layout.form-page 
    entity="glossary" 
    title="Edit Glossary Entry" 
    :back-route="route('glossary.show', $glossary)"
    :submit-route="route('glossary.update', $glossary)" 
    method="PUT">
    @include('glossary._form')
</x-layout.form-page>
@endsection
