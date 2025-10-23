@extends('layouts.app')

@section('content')
<x-layout.form-page 
    entity="translation" 
    title="Add Translation" 
    :back-route="route('glossaries.translations.index', $glossary)"
    :submit-route="route('glossaries.translations.store', $glossary)">
    @include('glossary-translation._form', ['translation' => null])
</x-layout.form-page>
@endsection
