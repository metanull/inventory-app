@extends('layouts.app')

@section('content')
<x-layout.form-page 
    entity="spelling" 
    title="Add Spelling" 
    :back-route="route('glossaries.spellings.index', $glossary)"
    :submit-route="route('glossaries.spellings.store', $glossary)">
    @include('glossary-spelling._form', ['spelling' => null])
</x-layout.form-page>
@endsection
