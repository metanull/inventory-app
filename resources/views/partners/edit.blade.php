@extends('layouts.app')

@section('content')
    <x-layout.form-page 
        entity="partners"
        title="Edit Partner"
        :back-route="route('partners.show', $partner)"
        :submit-route="route('partners.update', $partner)"
        method="PUT"
    >
        @include('partners._form', ['partner' => $partner])
    </x-layout.form-page>

    @push('scripts')
    <script>
        // Form change tracking disabled per user request
        console.log('Partners edit form loaded');
    </script>
    @endpush
@endsection
