@extends('layouts.app')

@section('content')
    <div class="max-w-3xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-8">
        @php($c = $entityColor('partners'))
        <div>
            <a href="{{ route('partners.show', $partner) }}" class="text-sm {{ $c['accentLink'] }}">&larr; Back to detail</a>
        </div>
        <x-entity.header entity="partners" title="Edit Partner" />

        <form method="POST" action="{{ route('partners.update', $partner) }}" class="bg-white shadow sm:rounded-lg p-6 space-y-6" id="edit-form">
            @method('PUT')
            @include('partners._form', ['partner' => $partner])
        </form>
    </div>

    @push('scripts')
    <script>
        // Form change tracking disabled per user request
        console.log('Partners edit form loaded');
    </script>
    @endpush
@endsection
