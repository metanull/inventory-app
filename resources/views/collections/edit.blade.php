@extends('layouts.app')

@section('content')
@php($c = $entityColor('collections'))
<div class="max-w-3xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <x-entity.header entity="collections" :title="'Edit: ' . ($collection->internal_name)" />

    <form method="POST" action="{{ route('collections.update', $collection) }}" class="bg-white shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg divide-y divide-gray-200">
        @method('PUT')
        @include('collections._form')
    </form>
</div>
@endsection
