@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-8">
    @php($c = $entityColor('languages'))
    <div><a href="{{ route('languages.show', $language) }}" class="text-sm {{ $c['accentLink'] }}">&larr; Back to detail</a></div>
    <x-entity.header entity="languages" title="Edit Language" />
    <form method="POST" action="{{ route('languages.update', $language) }}" class="bg-white shadow sm:rounded-lg p-6 space-y-6">
        @method('PUT')
        @include('languages._form', ['language' => $language])
    </form>
</div>
@endsection
