@extends('layouts.app')

@section('content')
    <x-layout.form-page 
        entity="partners"
        title="Create Partner"
        :back-route="route('partners.index')"
        :submit-route="route('partners.store')"
    >
        @include('partners._form', ['partner' => null])
    </x-layout.form-page>

    @push('scripts')
    <script>
        // Form change tracking disabled per user request
        console.log('Partners create form loaded');
    </script>
    @endpush
@endsection
