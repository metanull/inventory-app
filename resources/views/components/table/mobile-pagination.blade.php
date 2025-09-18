@props(['paginator'])

@if ($paginator->hasPages())
    <div class="flex flex-col sm:flex-row items-center justify-between px-4 py-3 bg-white border-t border-gray-200">
        <!-- Results summary -->
        <div class="flex items-center text-sm text-gray-700 mb-3 sm:mb-0">
            <span>
                Showing {{ $paginator->firstItem() }} to {{ $paginator->lastItem() }} of {{ $paginator->total() }} results
            </span>
        </div>

        <!-- Pagination links -->
        <div class="flex items-center space-x-2">
            <!-- Previous button -->
            @if ($paginator->onFirstPage())
                <span class="inline-flex items-center px-2 py-1 text-sm text-gray-400 bg-gray-100 rounded-md cursor-not-allowed">
                    <x-heroicon-o-chevron-left class="w-4 h-4" />
                    <span class="hidden sm:inline ml-1">Previous</span>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="inline-flex items-center px-2 py-1 text-sm text-gray-600 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                    <x-heroicon-o-chevron-left class="w-4 h-4" />
                    <span class="hidden sm:inline ml-1">Previous</span>
                </a>
            @endif

            <!-- Page numbers (hide on small screens) -->
            <div class="hidden sm:flex space-x-1">
                @foreach ($paginator->getUrlRange(max(1, $paginator->currentPage() - 2), min($paginator->lastPage(), $paginator->currentPage() + 2)) as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="inline-flex items-center px-3 py-1 text-sm bg-indigo-600 text-white rounded-md">
                            {{ $page }}
                        </span>
                    @else
                        <a href="{{ $url }}" class="inline-flex items-center px-3 py-1 text-sm text-gray-600 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                            {{ $page }}
                        </a>
                    @endif
                @endforeach
            </div>

            <!-- Current page indicator for mobile -->
            <div class="sm:hidden inline-flex items-center px-3 py-1 text-sm bg-gray-100 rounded-md">
                {{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}
            </div>

            <!-- Next button -->
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="inline-flex items-center px-2 py-1 text-sm text-gray-600 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                    <span class="hidden sm:inline mr-1">Next</span>
                    <x-heroicon-o-chevron-right class="w-4 h-4" />
                </a>
            @else
                <span class="inline-flex items-center px-2 py-1 text-sm text-gray-400 bg-gray-100 rounded-md cursor-not-allowed">
                    <span class="hidden sm:inline mr-1">Next</span>
                    <x-heroicon-o-chevron-right class="w-4 h-4" />
                </span>
            @endif
        </div>
    </div>
@endif