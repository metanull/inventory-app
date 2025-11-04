@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl mx-auto">
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
                                @if($setting['type'] === 'boolean')
                                    <x-form.checkbox 
                                        :name="$setting['key']"
                                        :label="$setting['label']"
                                        :checked="(bool)$setting['value']"
                                    >
                                        {{ $setting['description'] }}
                                    </x-form.checkbox>
                                @else
                                    <x-form.field 
                                        :label="$setting['label']" 
                                        :name="$setting['key']"
                                    >
                                        <x-form.input 
                                            :name="$setting['key']"
                                            :value="$setting['value']"
                                        />
                                        <p class="mt-1 text-sm text-gray-500">{{ $setting['description'] }}</p>
                                    </x-form.field>
                                @endif
                            @endforeach
                        </div>

                        <x-validation-errors class="mt-6" />

                        <x-form.actions 
                            :cancel-route="route('dashboard')"
                            entity="settings"
                            save-label="Save Settings"
                        />
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection