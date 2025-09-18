@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    @php($c = $entityColor('countries'))
    <x-entity.header entity="countries" title="Countries">
        <a href="{{ route('countries.create') }}" class="inline-flex items-center px-3 py-2 rounded-md {{ $c['button'] }} text-sm font-medium">
            <x-heroicon-o-plus class="w-5 h-5 mr-1" />
            Add Country
        </a>
    </x-entity.header>
    <livewire:tables.countries-table />
</div>
@endsection
@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        @php($c = $entityColor('countries'))
        <x-entity.header entity="countries" title="Countries">
            <a href="{{ route('countries.create') }}" class="inline-flex items-center px-3 py-2 rounded-md {{ $c['button'] }} text-sm font-medium">
                <x-heroicon-o-plus class="w-5 h-5 mr-1" /> Add Country
            </a>
        </x-entity.header>

        <livewire:tables.countries-table />
    </div>
@endsection
