@extends('layouts.app')

@section('content')
<x-layout.form-page 
    entity="collection_translations" 
    title="Edit Collection Translation" 
    :back-route="route('collection-translations.show', $collectionTranslation)"
    :submit-route="route('collection-translations.update', $collectionTranslation)"
    method="PUT">
    @include('collection-translations._form', ['collectionTranslation' => $collectionTranslation])
</x-layout.form-page>
@endsection
