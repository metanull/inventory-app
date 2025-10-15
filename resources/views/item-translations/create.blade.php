@extends('layouts.app')

@section('content')
<x-layout.form-page 
    entity="item_translations" 
    title="Create Item Translation" 
    :back-route="route('item-translations.index')"
    :submit-route="route('item-translations.store')">
    @include('item-translations._form', ['itemTranslation' => null])
</x-layout.form-page>
@endsection
