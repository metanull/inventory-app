@extends('layouts.app')

@section('content')
<x-layout.form-page 
    entity="spelling" 
    title="Edit Spelling" 
    :back-route="route('glossaries.spellings.show', [$glossary, $spelling])"
    :submit-route="route('glossaries.spellings.update', [$glossary, $spelling])" 
    method="PUT">
    @include('glossary-spelling._form')
</x-layout.form-page>
@endsection
