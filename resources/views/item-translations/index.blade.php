@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    @php($c = $entityColor('item_translations'))
    <x-entity.header entity="item_translations" title="Item Translations">
        <a href="{{ route('item-translations.create') }}" class="inline-flex items-center px-3 py-2 rounded-md {{ $c['button'] }} text-sm font-medium">
            <x-heroicon-o-plus class="w-5 h-5 mr-1" />
            Add Translation
        </a>
    </x-entity.header>

    @if(session('success'))
        <x-ui.alert :message="session('success')" type="success" entity="item_translations" />
    @endif

    <livewire:tables.item-translations-table />
</div>
@endsection
