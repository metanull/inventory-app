@props([
    'paginator',
    'perPageOptions' => config('interface.pagination.per_page_options'),
    'paramPage' => 'page',
    'paramPerPage' => 'per_page',
    'entity' => null,
])
@php
    $entityTheme = [
        'items' => [
            'focus' => 'focus:border-teal-500 focus:ring-teal-500',
            'border' => 'border-teal-300',
            'hover' => 'hover:bg-teal-50',
        ],
        'partners' => [
            'focus' => 'focus:border-yellow-500 focus:ring-yellow-500',
            'border' => 'border-yellow-300',
            'hover' => 'hover:bg-yellow-50',
        ],
    ];
    $theme = $entity && isset($entityTheme[$entity]) ? $entityTheme[$entity] : [
        'focus' => 'focus:border-indigo-500 focus:ring-indigo-500',
        'border' => 'border-gray-300',
        'hover' => 'hover:bg-gray-50',
    ];
@endphp

@if ($paginator instanceof \Illuminate\Contracts\Pagination\Paginator || $paginator instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
    <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <!-- Page size selector -->
        <form method="GET" class="flex items-center gap-2">
            @foreach(request()->except($paramPerPage, $paramPage) as $key => $value)
                <input type="hidden" name="{{ $key }}" value="{{ $value }}" />
            @endforeach
            <label class="text-sm text-gray-600">Rows per page</label>
            <select name="{{ $paramPerPage }}" class="block rounded-md border-gray-300 py-1.5 pl-2 pr-8 text-sm {{ $theme['focus'] }}" onchange="this.form.submit()">
                @foreach($perPageOptions as $opt)
                    <option value="{{ $opt }}" @selected($paginator->perPage() == $opt)>{{ $opt }}</option>
                @endforeach
            </select>
        </form>

        <!-- Pagination info + controls -->
        <div class="flex w-full items-center justify-between gap-3 sm:w-auto">
            <div class="text-sm text-gray-600">
                @if(method_exists($paginator, 'total'))
                    <span>Showing {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} of {{ $paginator->total() }}</span>
                @else
                    <span>Showing page {{ $paginator->currentPage() }}</span>
                @endif
            </div>
            <div class="inline-flex shadow-sm rounded-md" role="group">
                @php $prevDisabled = $paginator->onFirstPage(); @endphp
                <a href="{{ $prevDisabled ? '#' : $paginator->previousPageUrl() }}" class="px-3 py-1.5 text-sm font-medium bg-white border {{ $theme['border'] }} rounded-l-md {{ $theme['hover'] }} focus:z-10 {{ $prevDisabled ? 'opacity-50 cursor-not-allowed pointer-events-none' : '' }}">Previous</a>
                <span class="px-3 py-1.5 text-sm font-medium bg-white border-t border-b {{ $theme['border'] }}">Page {{ $paginator->currentPage() }}@if(method_exists($paginator, 'lastPage')) / {{ $paginator->lastPage() }} @endif</span>
                @php $nextDisabled = !$paginator->hasMorePages(); @endphp
                <a href="{{ $nextDisabled ? '#' : $paginator->nextPageUrl() }}" class="px-3 py-1.5 text-sm font-medium bg-white border {{ $theme['border'] }} rounded-r-md {{ $theme['hover'] }} focus:z-10 {{ $nextDisabled ? 'opacity-50 cursor-not-allowed pointer-events-none' : '' }}">Next</a>
            </div>
        </div>
    </div>
@endif
