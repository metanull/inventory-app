@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        @php($c = $entityColor('languages'))
        <x-entity.header entity="languages" title="Languages">
            <a href="{{ route('languages.create') }}" class="inline-flex items-center px-3 py-2 rounded-md {{ $c['button'] }} text-sm font-medium">
                <x-heroicon-o-plus class="w-5 h-5 mr-1" />
                Add Language
            </a>
        </x-entity.header>

        @if(session('status'))
            <x-ui.alert :message="session('status')" type="success" entity="languages" />
        @endif

        <livewire:tables.languages-table />
    </div>
@endsection
