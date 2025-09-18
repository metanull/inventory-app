@extends('layouts.app')

@section('content')
    <x-layout.form-page 
        entity="items"
        title="Create Item"
        :back-route="route('items.index')"
        :submit-route="route('items.store')"
    >
        @include('items._form', ['item' => null])
    </x-layout.form-page>

    @push('scripts')
    <script>
        // Form change tracking disabled per user request
        console.log('Items create form loaded');
    </script>
    @endpush
@endsection
