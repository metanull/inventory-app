@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-8">
    @php($c = $entityColor('countries'))
    <div><a href="{{ route('countries.index') }}" class="text-sm {{ $c['accentLink'] }}">&larr; Back to list</a></div>
    <x-entity.header entity="countries" :title="$country->internal_name">
        <a href="{{ route('countries.edit', $country) }}" class="inline-flex items-center px-3 py-2 rounded-md {{ $c['button'] }} text-sm font-medium">Edit</a>
        <form method="POST" action="{{ route('countries.destroy', $country) }}" style="display:inline" onsubmit="return confirm('Delete this country?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="inline-flex items-center px-3 py-2 rounded-md bg-red-600 hover:bg-red-700 text-white text-sm font-medium">Delete</button>
        </form>
        @if($country->backward_compatibility)
            <span class="px-2 py-0.5 text-xs rounded {{ $c['badge'] }}">Legacy: {{ $country->backward_compatibility }}</span>
        @endif
    </x-entity.header>

    <div class="bg-white shadow sm:rounded-lg overflow-hidden">
        <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
            <h2 class="text-lg font-medium text-gray-900">Information</h2>
        </div>
        <div class="px-4 py-6 sm:px-6 space-y-6">
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <dt class="text-sm font-medium text-gray-500">ID (ISO 3166-1 alpha-3)</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $country->id }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Internal Name</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $country->internal_name }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Backward Compatibility</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $country->backward_compatibility ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Created / Updated</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ optional($country->created_at)->format('Y-m-d H:i') }} / {{ optional($country->updated_at)->format('Y-m-d H:i') }}</dd>
                </div>
            </dl>
        </div>
    </div>
</div>
@endsection
@extends('layouts.app')

@section('content')
    <div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-8">
        @php($c = $entityColor('countries'))
        <div><a href="{{ route('countries.index') }}" class="text-sm {{ $c['accentLink'] }}">&larr; Back to list</a></div>
        <x-entity.header entity="countries" :title="$country->internal_name">
            <a href="{{ route('countries.edit', $country) }}" class="inline-flex items-center px-3 py-2 rounded-md {{ $c['button'] }} text-sm font-medium">Edit</a>
            <form method="POST" action="{{ route('countries.destroy', $country) }}" style="display:inline" onsubmit="return confirm('Delete this country?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center px-3 py-2 rounded-md bg-red-600 hover:bg-red-700 text-white text-sm font-medium">Delete</button>
            </form>
        </x-entity.header>

        <div class="bg-white shadow sm:rounded-lg p-6 space-y-6">
            <dl class="divide-y divide-gray-100">
                <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:py-5">
                    <dt class="text-sm font-medium text-gray-600">ISO Code</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">{{ $country->id }}</dd>
                </div>
                <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:py-5">
                    <dt class="text-sm font-medium text-gray-600">Internal Name</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">{{ $country->internal_name }}</dd>
                </div>
                <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:py-5">
                    <dt class="text-sm font-medium text-gray-600">Legacy ID</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">{{ $country->backward_compatibility ?? '—' }}</dd>
                </div>
                <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:py-5">
                    <dt class="text-sm font-medium text-gray-600">Created At</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">{{ optional($country->created_at)->format('Y-m-d H:i') }}</dd>
                </div>
                <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:py-5">
                    <dt class="text-sm font-medium text-gray-600">Updated At</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">{{ optional($country->updated_at)->format('Y-m-d H:i') }}</dd>
                </div>
            </dl>
        </div>
    </div>
@endsection
