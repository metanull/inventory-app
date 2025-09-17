@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        @php($c = $entityColor('collections'))
        <x-entity.header entity="collections" title="Collections">
            <a href="{{ route('collections.create') }}" class="inline-flex items-center px-3 py-2 rounded-md {{ $c['button'] }} text-sm font-medium">
                <x-heroicon-o-plus class="w-5 h-5 mr-1" />
                Add Collection
            </a>
        </x-entity.header>

        <livewire:tables.collections-table />
    </div>
@endsection
