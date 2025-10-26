@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <x-entity.header entity="collection_translations" title="Collection Translations">
        @can(\App\Enums\Permission::CREATE_DATA->value)
            <x-ui.button 
                href="{{ route('collection-translations.create') }}" 
                variant="primary" 
                entity="collection_translations"
                icon="plus">
                Add Translation
            </x-ui.button>
        @endcan
    </x-entity.header>

    @if(session('status'))
        <x-ui.alert :message="session('status')" type="success" entity="collection_translations" />
    @endif

    <livewire:tables.collection-translations-table />
</div>
@endsection
