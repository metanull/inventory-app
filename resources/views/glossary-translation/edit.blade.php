@extends('layouts.app')

@section('content')
<x-layout.form-page 
    entity="translation" 
    title="Edit Translation" 
    :back-route="route('glossaries.translations.show', [$glossary, $translation])"
    :submit-route="route('glossaries.translations.update', [$glossary, $translation])" 
    method="PUT">
    @include('glossary-translation._form')
</x-layout.form-page>
@endsection
