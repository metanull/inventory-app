@extends('layouts.app')

@section('content')
    <x-layout.show-page 
        entity="languages"
        :title="$language->internal_name"
        :back-route="route('languages.index')"
        :edit-route="route('languages.edit', $language)"
        :delete-route="route('languages.destroy', $language)"
        delete-confirm="Delete this language?"
        :backward-compatibility="$language->backward_compatibility"
        :badges="$language->is_default ? ['Default'] : []"
    >
        <x-display.description-list>
            <x-display.field label="ID (ISO 639-3)" :value="$language->id" />
            <x-display.field label="Internal Name" :value="$language->internal_name" />
            <x-display.field label="Backward Compatibility" :value="$language->backward_compatibility" />
            <x-display.field label="Default" :value="$language->is_default ? 'Yes' : 'No'" />
            <x-display.field label="Created At">
                <x-display.timestamp :datetime="$language->created_at" />
            </x-display.field>
            <x-display.field label="Updated At">
                <x-display.timestamp :datetime="$language->updated_at" />
            </x-display.field>
        </x-display.description-list>
    </x-layout.show-page>
@endsection
@extends('layouts.app')

@section('content')
    <div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-8">
        @php($c = $entityColor('languages'))
        <div><a href="{{ route('languages.index') }}" class="text-sm {{ $c['accentLink'] }}">&larr; Back to list</a></div>
        <x-entity.header entity="languages" :title="$language->internal_name">
            <a href="{{ route('languages.edit', $language) }}" class="inline-flex items-center px-3 py-2 rounded-md {{ $c['button'] }} text-sm font-medium">Edit</a>
            <form method="POST" action="{{ route('languages.destroy', $language) }}" style="display:inline" onsubmit="return confirm('Delete this language?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center px-3 py-2 rounded-md bg-red-600 hover:bg-red-700 text-white text-sm font-medium">Delete</button>
            </form>
        </x-entity.header>

        <div class="bg-white shadow sm:rounded-lg p-6 space-y-6">
            <dl class="divide-y divide-gray-100">
                <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:py-5"><dt class="text-sm font-medium text-gray-600">ISO Code</dt><dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">{{ $language->id }}</dd></div>
                <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:py-5"><dt class="text-sm font-medium text-gray-600">Internal Name</dt><dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">{{ $language->internal_name }}</dd></div>
                <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:py-5"><dt class="text-sm font-medium text-gray-600">Default</dt><dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">{{ $language->is_default ? 'Yes' : 'No' }}</dd></div>
                <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:py-5"><dt class="text-sm font-medium text-gray-600">Legacy ID</dt><dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">{{ $language->backward_compatibility ?? '—' }}</dd></div>
                <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:py-5"><dt class="text-sm font-medium text-gray-600">Created At</dt><dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">{{ optional($language->created_at)->format('Y-m-d H:i') }}</dd></div>
                <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:py-5"><dt class="text-sm font-medium text-gray-600">Updated At</dt><dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">{{ optional($language->updated_at)->format('Y-m-d H:i') }}</dd></div>
            </dl>
        </div>
    </div>
@endsection
