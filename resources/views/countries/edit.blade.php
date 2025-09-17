@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-8">
    @php($c = $entityColor('countries'))
    <div><a href="{{ route('countries.show', $country) }}" class="text-sm {{ $c['accentLink'] }}">&larr; Back to detail</a></div>
    <x-entity.header entity="countries" title="Edit Country" />
    <form method="POST" action="{{ route('countries.update', $country) }}" class="bg-white shadow sm:rounded-lg p-6 space-y-6">
        @method('PUT')
        @include('countries._form', ['country' => $country])
    </form>
</div>
@endsection
