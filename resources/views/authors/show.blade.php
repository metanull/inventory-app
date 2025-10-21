@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-8">
    @php($c = $entityColor('authors'))
    
    <div>
        <a href="{{ route('authors.index') }}" class="text-sm {{ $c['accentLink'] }}">&larr; Back to list</a>
    </div>
    
    <x-entity.header entity="authors" title="Author Details">
        <div class="flex gap-2">
            @can(\App\Enums\Permission::UPDATE_DATA->value)
                <a href="{{ route('authors.edit', $author) }}" class="inline-flex items-center px-3 py-2 rounded-md {{ $c['button'] }} text-sm font-medium">
                    <x-heroicon-o-pencil class="w-5 h-5 mr-1" />
                    Edit
                </a>
            @endcan
            @can(\App\Enums\Permission::DELETE_DATA->value)
                <form method="POST" action="{{ route('authors.destroy', $author) }}" onsubmit="return confirm('Are you sure you want to delete this author?');">
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
        <x-ui.alert :message="session('success')" type="success" entity="authors" />
    @endif

    <div class="bg-white shadow sm:rounded-lg">
        <div class="px-6 py-5">
            <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">Name</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $author->name }}</dd>
                </div>

                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">Internal Name</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $author->internal_name ?? '-' }}</dd>
                </div>

                @if($author->backward_compatibility)
                    <div class="sm:col-span-1">
                        <dt class="text-sm font-medium text-gray-500">Legacy ID</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $author->backward_compatibility }}</dd>
                    </div>
                @endif

                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">Created</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $author->created_at->format('Y-m-d H:i') }}</dd>
                </div>

                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">Last Updated</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $author->updated_at->format('Y-m-d H:i') }}</dd>
                </div>
            </dl>
        </div>
    </div>
</div>
@endsection
