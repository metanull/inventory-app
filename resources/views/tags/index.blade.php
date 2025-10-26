@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <x-entity.header entity="tags" title="Tags">
            @can(\App\Enums\Permission::CREATE_DATA->value)
                <x-ui.button 
                    href="{{ route('tags.create') }}" 
                    variant="primary" 
                    entity="tags"
                    icon="plus">
                    Add Tag
                </x-ui.button>
            @endcan
        </x-entity.header>

        @if(session('status'))
            <x-ui.alert :message="session('status')" type="success" entity="tags" />
        @endif

        <livewire:tables.tags-table />
    </div>
@endsection
