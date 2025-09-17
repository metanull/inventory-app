@extends('layouts.app')

@section('content')
    <div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-8">
        @php($c = $entityColor('items'))
        <div>
            <a href="{{ route('items.index') }}" class="text-sm {{ $c['accentLink'] }}">&larr; Back to list</a>
        </div>
        <x-entity.header entity="items" :title="$item->internal_name">
            <a href="{{ route('items.edit', $item) }}" class="inline-flex items-center px-3 py-2 rounded-md {{ $c['button'] }} text-sm font-medium">Edit</a>
            <form method="POST" action="{{ route('items.destroy', $item) }}" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this item?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center px-3 py-2 rounded-md bg-red-600 hover:bg-red-700 text-white text-sm font-medium">Delete</button>
            </form>
            @if($item->backward_compatibility)
                <span class="px-2 py-0.5 text-xs rounded {{ $c['badge'] }}">Legacy: {{ $item->backward_compatibility }}</span>
            @endif
        </x-entity.header>

    <div class="bg-white shadow sm:rounded-lg overflow-hidden">
            <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                <h2 class="text-lg font-medium text-gray-900">Information</h2>
            </div>
            <div class="px-4 py-6 sm:px-6 space-y-6">
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Internal Name</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $item->internal_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Backward Compatibility</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $item->backward_compatibility ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Created At</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ optional($item->created_at)->format('Y-m-d H:i') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Updated At</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ optional($item->updated_at)->format('Y-m-d H:i') }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
@endsection
