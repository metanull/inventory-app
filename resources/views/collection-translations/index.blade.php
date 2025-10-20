@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    @php($c = $entityColor('collection_translations'))
    <x-entity.header entity="collection_translations" title="Collection Translations">
        @can(\App\Enums\Permission::CREATE_DATA->value)
            <a href="{{ route('collection-translations.create') }}" class="inline-flex items-center px-3 py-2 rounded-md {{ $c['button'] }} text-sm font-medium">
                <x-heroicon-o-plus class="w-5 h-5 mr-1" />
                Add Translation
            </a>
        @endcan
    </x-entity.header>

    @if(session('success'))
        <x-ui.alert :message="session('success')" type="success" entity="collection_translations" />
    @endif

    <livewire:tables.collection-translations-table />
</div>
@endsection
