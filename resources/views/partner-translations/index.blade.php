@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    @php($c = $entityColor('partner_translations'))
    <x-entity.header entity="partner_translations" title="Partner Translations">
        @can(\App\Enums\Permission::CREATE_DATA->value)
            <a href="{{ route('partner-translations.create') }}" class="inline-flex items-center px-3 py-2 rounded-md {{ $c['button'] }} text-sm font-medium">
                <x-heroicon-o-plus class="w-5 h-5 mr-1" />
                Add Translation
            </a>
        @endcan
    </x-entity.header>

    @if(session('success'))
        <x-ui.alert :message="session('success')" type="success" entity="partner_translations" />
    @endif

    <livewire:tables.partner-translations-table />
</div>
@endsection
