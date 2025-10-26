@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    @php($c = $entityColor('available-images'))
    
    <x-entity.header entity="available-images" title="Available Images" />

    @if(session('success'))
        <x-ui.alert :message="session('success')" type="success" entity="available-images" />
    @endif

    <!-- Search Bar -->
    <div class="mb-4 bg-white shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg p-4">
        <form method="GET" action="{{ route('available-images.index') }}" class="flex flex-wrap items-center gap-3">
            <div class="relative flex-1 min-w-[16rem]">
                <input 
                    type="text" 
                    name="q" 
                    value="{{ $search }}" 
                    placeholder="Search by comment..." 
                    class="w-full rounded-md border-gray-300 focus:border-pink-500 focus:ring-pink-500"
                />
            </div>
            
            <div class="flex items-center gap-2">
                <x-ui.button type="submit" variant="primary" entity="available-images" size="sm">
                    Search
                </x-ui.button>
                
                @if($search)
                    <x-ui.button href="{{ route('available-images.index') }}" variant="secondary" size="sm">
                        Clear
                    </x-ui.button>
                @endif
            </div>
        </form>
    </div>

    <!-- Images Grid -->
    @if($availableImages->isEmpty())
        <div class="bg-white rounded-lg shadow p-12 text-center">
            <x-heroicon-o-photo class="mx-auto h-12 w-12 text-gray-400" />
            <h3 class="mt-2 text-sm font-medium text-gray-900">No images found</h3>
            <p class="mt-1 text-sm text-gray-500">
                @if($search)
                    Try adjusting your search terms.
                @else
                    No processed images available yet. Images are automatically added here after being uploaded and processed.
                @endif
            </p>
        </div>
    @else
        <div class="bg-white shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg overflow-hidden">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 p-4">
                @foreach($availableImages as $image)
                    <a href="{{ route('available-images.show', $image) }}" 
                       class="group relative bg-white rounded-lg border-2 {{ $c['border'] ?? 'border-pink-200' }} hover:shadow-lg transition-shadow cursor-pointer">
                        <!-- Image -->
                        <div class="aspect-square rounded-t-lg overflow-hidden bg-gray-100">
                            <img 
                                src="{{ route('available-images.view', $image) }}" 
                                alt="{{ $image->comment ?: 'Image' }}"
                                class="h-full w-full object-cover group-hover:scale-105 transition-transform duration-200"
                                onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22400%22 height=%22400%22%3E%3Crect fill=%22%23f3f4f6%22 width=%22400%22 height=%22400%22/%3E%3Ctext fill=%22%239ca3af%22 font-family=%22sans-serif%22 font-size=%2224%22 text-anchor=%22middle%22 x=%22200%22 y=%22200%22%3ENo Image%3C/text%3E%3C/svg%3E'"
                            />
                        </div>

                        <!-- Content -->
                        <div class="p-3">
                            <p class="text-sm font-medium text-gray-900 truncate mb-1">
                                {{ $image->comment ?: 'No comment' }}
                            </p>
                            <p class="text-xs text-gray-500">
                                {{ $image->created_at->format('M d, Y') }}
                            </p>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            <x-layout.pagination 
                :paginator="$availableImages" 
                entity="available-images"
                param-page="page"
            />
        </div>
    @endif
</div>
@endsection
