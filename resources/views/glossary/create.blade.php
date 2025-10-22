@extends('layouts.app')

@section('content')
<x-layout.form-page 
    entity="glossary" 
    title="Create Glossary Entry" 
    :back-route="route('glossary.index')"
    :submit-route="route('glossary.store')">
    @include('glossary._form', ['glossary' => null])
</x-layout.form-page>
@endsection
