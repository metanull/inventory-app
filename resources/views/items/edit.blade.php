@extends('layouts.app')

@section('content')
    <x-layout.form-page 
        entity="items"
        title="Edit Item"
        :back-route="route('items.show', $item)"
        :submit-route="route('items.update', $item)"
        method="PUT"
    >
        @include('items._form', ['item' => $item])
    </x-layout.form-page>

    @push('scripts')
    <script>
        // Form change tracking disabled per user request
        console.log('Items edit form loaded');
    </script>
    @endpush
@endsection
