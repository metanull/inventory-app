@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <x-entity.header entity="users" title="API Tokens">
            <p class="text-sm text-gray-600">
                Manage API tokens to access the application programmatically.
            </p>
        </x-entity.header>

        <div>
            @livewire('api.api-token-manager')
        </div>
    </div>
@endsection
