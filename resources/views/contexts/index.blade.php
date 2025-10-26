@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <x-entity.header entity="contexts" title="Contexts">
        @can(\App\Enums\Permission::CREATE_DATA->value)
            <x-ui.button 
                href="{{ route('contexts.create') }}" 
                variant="primary" 
                entity="contexts"
                icon="plus">
                Add Context
            </x-ui.button>
        @endcan
    </x-entity.header>

    @if(session('status'))
        <x-ui.alert :message="session('status')" type="success" entity="contexts" />
    @endif

    <livewire:tables.contexts-table />
</div>
@endsection
