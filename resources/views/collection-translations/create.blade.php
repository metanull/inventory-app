@extends('layouts.app')

@section('content')
<x-layout.form-page 
    entity="collection_translations" 
    title="Create Collection Translation" 
    :back-route="route('collection-translations.index')"
    :submit-route="route('collection-translations.store')">
    @include('collection-translations._form', ['collectionTranslation' => null])
</x-layout.form-page>
@endsection
