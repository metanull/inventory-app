@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <x-entity.header entity="partners" title="Partners">
            @can(\App\Enums\Permission::CREATE_DATA->value)
                <x-ui.button 
                    href="{{ route('partners.create') }}" 
                    variant="primary" 
                    entity="partners"
                    icon="plus">
                    Add Partner
                </x-ui.button>
            @endcan
        </x-entity.header>

        @if(session('status'))
            <x-ui.alert :message="session('status')" type="success" entity="partners" />
        @endif

        <livewire:tables.partners-table />
    </div>
@endsection
