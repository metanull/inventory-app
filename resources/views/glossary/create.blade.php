@extends('layouts.app')

@section('content')
<x-layout.form-page 
    entity="glossary" 
    title="Create Glossary Entry" 
    :back-route="route('glossaries.index')"
    :submit-route="route('glossaries.store')">
    @include('glossary._form', ['glossary' => null])
</x-layout.form-page>
@endsection
