@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl mx-auto">
            @php($c = $entityColor('settings'))
            
            <x-entity.header entity="settings" title="System Settings">
                <p class="text-sm text-gray-600">
                    Configure system-wide settings and behavior.
                </p>
            </x-entity.header>

            @if(session('success'))
                <x-ui.alert type="success" :message="session('success')" entity="settings" />
            @endif

            <div class="bg-white shadow-sm rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <form method="POST" action="{{ route('settings.update') }}">
                        @csrf
                        @method('PUT')

                        <div class="space-y-6">
                            @foreach($settings as $setting)
                                <div class="flex items-start">
                                    <div class="flex items-center h-5">
                                        <input 
                                            id="{{ $setting['key'] }}" 
                                            name="{{ $setting['key'] }}" 
                                            type="{{ $setting['type'] === 'boolean' ? 'checkbox' : 'text' }}"
                                            value="{{ $setting['type'] === 'boolean' ? '1' : $setting['value'] }}"
                                            {{ $setting['type'] === 'boolean' && $setting['value'] ? 'checked' : '' }}
                                            class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded {{ $setting['type'] === 'boolean' ? '' : 'block w-full' }}"
                                        >
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="{{ $setting['key'] }}" class="font-medium text-gray-700">
                                            {{ $setting['label'] }}
                                        </label>
                                        <p class="text-gray-500">{{ $setting['description'] }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        @if($errors->any())
                            <div class="mt-6 bg-red-50 border border-red-200 rounded-md p-4">
                                <div class="flex">
                                    <div class="shrink-0">
                                        <x-heroicon-o-exclamation-circle class="h-5 w-5 text-red-400" />
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-red-800">
                                            There were errors with your submission
                                        </h3>
                                        <div class="mt-2 text-sm text-red-700">
                                            <ul class="list-disc pl-5 space-y-1">
                                                @foreach ($errors->all() as $error)
                                                    <li>{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="mt-6 flex justify-end">
                            <x-ui.button type="submit" variant="primary" entity="settings">
                                Save Settings
                            </x-ui.button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection