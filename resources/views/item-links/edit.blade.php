@extends('layouts.app')

@section('content')
<x-layout.form-page 
    entity="item_item_links" 
    title="Edit Item Link" 
    :back-route="route('item-links.index', $item)"
    :submit-route="route('item-links.update', [$item, $itemItemLink])">
    @method('PUT')
    @include('item-links._form')
</x-layout.form-page>
@endsection
