@extends('layouts.app')

@section('content')
    <x-layout.index-page 
        entity="item-translations" 
        title="Item Translations"
        createButtonText="Add Translation"
    />
@endsection
