@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl mx-auto">
            <div class="bg-white shadow-sm rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                        System Settings
                    </h3>
                    <p class="text-sm text-gray-600 mb-6">
                        Configure system-wide settings and behavior.
                    </p>

                    @if(session('success'))
                        <x-ui.alert type="success" :message="session('success')" />
                    @endif

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
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                        </svg>
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
                            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Save Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection