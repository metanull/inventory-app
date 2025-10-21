@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    @php($c = $entityColor('authors'))
    <x-entity.header entity="authors" title="Authors">
        @can(\App\Enums\Permission::CREATE_DATA->value)
            <a href="{{ route('authors.create') }}" class="inline-flex items-center px-3 py-2 rounded-md {{ $c['button'] }} text-sm font-medium">
                <x-heroicon-o-plus class="w-5 h-5 mr-1" />
                Add Author
            </a>
        @endcan
    </x-entity.header>

    @if(session('success'))
        <x-ui.alert :message="session('success')" type="success" entity="authors" />
    @endif

    <!-- Search Bar -->
    <div class="mb-6 bg-white shadow sm:rounded-lg p-4">
        <form method="GET" action="{{ route('authors.index') }}" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-0">
                <input type="text" name="q" value="{{ $search ?? '' }}" 
                    placeholder="Search authors..." 
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
            </div>
            <button type="submit" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Search
            </button>
            @if($search)
                <a href="{{ route('authors.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                    Clear
                </a>
            @endif
        </form>
    </div>

    <div class="bg-white shadow sm:rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Internal Name</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($authors as $author)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            <a href="{{ route('authors.show', $author) }}" class="hover:text-indigo-600">
                                {{ $author->name }}
                            </a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $author->internal_name ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="{{ route('authors.show', $author) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">View</a>
                            @can(\App\Enums\Permission::UPDATE_DATA->value)
                                <a href="{{ route('authors.edit', $author) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-6 py-4 text-center text-sm text-gray-500">
                            No authors found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($authors->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $authors->links() }}
            </div>
        @endif
    </div>
</div>
@endsection