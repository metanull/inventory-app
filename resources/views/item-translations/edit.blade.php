@extends('layouts.app')

@section('content')
<x-layout.form-page 
    entity="item_translations" 
    title="Edit Item Translation" 
    :back-route="route('item-translations.show', $itemTranslation)"
    :submit-route="route('item-translations.update', $itemTranslation)"
    method="PUT">
    @include('item-translations._form', ['itemTranslation' => $itemTranslation])
</x-layout.form-page>
@endsection
