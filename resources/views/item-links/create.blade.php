@extends('layouts.app')

@section('content')
<x-layout.form-page 
    entity="item_item_links" 
    title="Create Item Link" 
    :back-route="route('item-links.index', $item)"
    :submit-route="route('item-links.store', $item)">
    @include('item-links._form', ['itemItemLink' => null])
</x-layout.form-page>
@endsection
