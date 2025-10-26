@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <x-entity.header entity="items" title="Items">
            @can(\App\Enums\Permission::CREATE_DATA->value)
                <x-ui.button 
                    href="{{ route('items.create') }}" 
                    variant="primary" 
                    entity="items"
                    icon="plus">
                    Add Item
                </x-ui.button>
            @endcan
        </x-entity.header>

        @if(session('status'))
            <x-ui.alert :message="session('status')" type="success" entity="items" />
        @endif

        <livewire:tables.items-table />
    </div>
@endsection
