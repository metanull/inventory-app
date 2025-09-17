@extends('layouts.app')

@section('content')
    <div class="max-w-3xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-8">
        @php($c = $entityColor('items'))
        <div>
            <a href="{{ route('items.index') }}" class="text-sm {{ $c['accentLink'] }}">&larr; Back to list</a>
        </div>
        <x-entity.header entity="items" title="Create Item" />

        <form method="POST" action="{{ route('items.store') }}" class="bg-white shadow sm:rounded-lg p-6 space-y-6" id="create-form">
            @include('items._form', ['item' => null])
        </form>
    </div>

    @push('scripts')
    <script>
        // Form change tracking disabled per user request
        console.log('Items create form loaded');
    </script>
    @endpush
@endsection
