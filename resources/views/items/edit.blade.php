@extends('layouts.app')

@section('content')
    <div class="max-w-3xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-8">
        @php($c = $entityColor('items'))
        <div>
            <a href="{{ route('items.show', $item) }}" class="text-sm {{ $c['accentLink'] }}">&larr; Back to detail</a>
        </div>
        <x-entity.header entity="items" title="Edit Item" />

        <form method="POST" action="{{ route('items.update', $item) }}" class="bg-white shadow sm:rounded-lg p-6 space-y-6" id="edit-form">
            @method('PUT')
            @include('items._form', ['item' => $item])
        </form>
    </div>

    @push('scripts')
    <script>
        // Form change tracking disabled per user request
        console.log('Items edit form loaded');
    </script>
    @endpush
@endsection
