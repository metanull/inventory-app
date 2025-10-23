@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        @php($c = $entityColor('tags'))
        <x-entity.header entity="tags" title="Tags">
            @can(\App\Enums\Permission::CREATE_DATA->value)
                <a href="{{ route('tags.create') }}" class="inline-flex items-center px-3 py-2 rounded-md {{ $c['button'] }} text-sm font-medium">
                    <x-heroicon-o-plus class="w-5 h-5 mr-1" />
                    Add Tag
                </a>
            @endcan
        </x-entity.header>

        @if(session('status'))
            <x-ui.alert :message="session('status')" type="success" entity="tags" />
        @endif

        <livewire:tables.tags-table />
    </div>
@endsection
