@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <x-entity.header entity="collections" title="Collections">
            @can(\App\Enums\Permission::CREATE_DATA->value)
                <x-ui.button 
                    href="{{ route('collections.create') }}" 
                    variant="primary" 
                    entity="collections"
                    icon="plus">
                    Add Collection
                </x-ui.button>
            @endcan
        </x-entity.header>

        @if(session('status'))
            <x-ui.alert :message="session('status')" type="success" entity="collections" />
        @endif

        <livewire:tables.collections-table />
    </div>
@endsection
