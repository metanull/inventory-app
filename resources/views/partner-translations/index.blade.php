@extends('layouts.app')

@section('content')
    <x-layout.index-page 
        entity="partner-translations" 
        title="Partner Translations"
        createButtonText="Add Translation"
    />
@endsection
