@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <x-entity.header entity="glossary" title="Glossary">
            @can(\App\Enums\Permission::CREATE_DATA->value)
                <x-ui.button 
                    href="{{ route('glossaries.create') }}" 
                    variant="primary" 
                    entity="glossary"
                    icon="plus">
                    Add Entry
                </x-ui.button>
            @endcan
        </x-entity.header>

        @if(session('status'))
            <x-ui.alert :message="session('status')" type="success" entity="glossary" />
        @endif

        <livewire:tables.glossary-table />
    </div>
@endsection
