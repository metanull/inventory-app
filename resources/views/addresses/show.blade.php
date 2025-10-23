@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-8">
    @php($c = $entityColor('addresses'))
    
    <div>
        <a href="{{ route('addresses.index') }}" class="text-sm {{ $c['accentLink'] }}">&larr; Back to list</a>
    </div>
    
    <x-entity.header entity="addresses" title="Address Details">
        <div class="flex gap-2">
            @can(\App\Enums\Permission::UPDATE_DATA->value)
                <a href="{{ route('addresses.edit', $address) }}" class="inline-flex items-center px-3 py-2 rounded-md {{ $c['button'] }} text-sm font-medium">
                    <x-heroicon-o-pencil class="w-5 h-5 mr-1" />
                    Edit
                </a>
            @endcan
            @can(\App\Enums\Permission::DELETE_DATA->value)
                <form method="POST" action="{{ route('addresses.destroy', $address) }}" onsubmit="return confirm('Are you sure you want to delete this address?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex items-center px-3 py-2 rounded-md bg-red-600 hover:bg-red-700 text-white text-sm font-medium">
                        <x-heroicon-o-trash class="w-5 h-5 mr-1" />
                        Delete
                    </button>
                </form>
            @endcan
        </div>
    </x-entity.header>

    @if(session('success'))
        <x-ui.alert :message="session('success')" type="success" entity="addresses" />
    @endif

    <div class="bg-white shadow sm:rounded-lg">
        <div class="px-6 py-5">
            <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <dt class="text-sm font-medium text-gray-500">Internal Name</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $address->internal_name }}</dd>
                </div>

                <div class="sm:col-span-2">
                    <dt class="text-sm font-medium text-gray-500">Country</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $address->country->internal_name }}</dd>
                </div>

                @if($address->translations->count() > 0)
                    <div class="sm:col-span-2">
                        <dt class="text-sm font-medium text-gray-500 mb-2">Translations</dt>
                        <dd class="mt-1 space-y-4">
                            @foreach($address->translations as $translation)
                                <div class="p-4 bg-gray-50 rounded-lg">
                                    <div class="text-sm font-medium text-gray-900 mb-2">
                                        {{ $translation->language->internal_name }}
                                    </div>
                                    <div class="text-sm text-gray-700 mb-2">
                                        {{ $translation->address }}
                                    </div>
                                    @if($translation->description)
                                        <div class="text-sm text-gray-500 italic">
                                            {{ $translation->description }}
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </dd>
                    </div>
                @endif

                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">Created</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $address->created_at->format('Y-m-d H:i') }}</dd>
                </div>

                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">Last Updated</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $address->updated_at->format('Y-m-d H:i') }}</dd>
                </div>
            </dl>
        </div>
    </div>
</div>
@endsection
