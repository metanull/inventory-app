@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <x-entity.header entity="authors" title="Authors">
        @can(\App\Enums\Permission::CREATE_DATA->value)
            <x-ui.button 
                href="{{ route('authors.create') }}" 
                variant="primary" 
                entity="authors"
                icon="plus">
                Add Author
            </x-ui.button>
        @endcan
    </x-entity.header>

    @if(session('status'))
        <x-ui.alert :message="session('status')" type="success" entity="authors" />
    @endif

    <livewire:tables.authors-table />
</div>
@endsection
