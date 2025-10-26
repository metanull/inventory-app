@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <x-entity.header entity="partner_translations" title="Partner Translations">
        @can(\App\Enums\Permission::CREATE_DATA->value)
            <x-ui.button 
                href="{{ route('partner-translations.create') }}" 
                variant="primary" 
                entity="partner_translations"
                icon="plus">
                Add Translation
            </x-ui.button>
        @endcan
    </x-entity.header>

    @if(session('status'))
        <x-ui.alert :message="session('status')" type="success" entity="partner_translations" />
    @endif

    <livewire:tables.partner-translations-table />
</div>
@endsection
