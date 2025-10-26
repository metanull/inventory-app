@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <x-entity.header entity="languages" title="Languages">
            @can(\App\Enums\Permission::CREATE_DATA->value)
                <x-ui.button 
                    href="{{ route('languages.create') }}" 
                    variant="primary" 
                    entity="languages"
                    icon="plus">
                    Add Language
                </x-ui.button>
            @endcan
        </x-entity.header>

        @if(session('status'))
            <x-ui.alert :message="session('status')" type="success" entity="languages" />
        @endif

        <livewire:tables.languages-table />
    </div>
@endsection
