@extends('layouts.app')

@section('content')
    <x-layout.index-page 
        entity="glossary" 
        createRoute="{{ route('glossaries.create') }}" 
        createButtonText="Add Entry" 
    />
@endsection
