@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <x-entity.header entity="countries" title="Countries">
            @can(\App\Enums\Permission::CREATE_DATA->value)
                <x-ui.button 
                    href="{{ route('countries.create') }}" 
                    variant="primary" 
                    entity="countries"
                    icon="plus">
                    Add Country
                </x-ui.button>
            @endcan
        </x-entity.header>

        @if(session('status'))
            <x-ui.alert :message="session('status')" type="success" entity="countries" />
        @endif

        <livewire:tables.countries-table />
    </div>
@endsection
