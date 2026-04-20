@props([
    'paginator',
    'action',
    'query' => [],
    'currentPerPage' => null,
    'perPageOptions' => config('interface.pagination.per_page_options'),
    'pageParam' => 'page',
    'perPageParam' => 'per_page',
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
        'collections' => [
            'focus' => 'focus:border-indigo-500 focus:ring-indigo-500',
            'border' => 'border-indigo-300',
            'hover' => 'hover:bg-indigo-50',
        ],
    ];

    $theme = $entity && isset($entityTheme[$entity])
        ? $entityTheme[$entity]
        : [
            'focus' => 'focus:border-gray-500 focus:ring-gray-500',
            'border' => 'border-gray-300',
            'hover' => 'hover:bg-gray-50',
        ];

    $renderHidden = function (string $name, mixed $value) use (&$renderHidden): void {
        if (is_array($value)) {
            foreach ($value as $key => $nestedValue) {
                $renderHidden($name.'['.$key.']', $nestedValue);
            }

            return;
        }

        if ($value === null || $value === '') {
            return;
        }

        echo '<input type="hidden" name="'.e($name).'" value="'.e((string) $value).'" />';
    };

    $selectedPerPage = $currentPerPage ?? (method_exists($paginator, 'perPage') ? $paginator->perPage() : config('interface.pagination.default_per_page'));
@endphp

@if ($paginator instanceof \Illuminate\Contracts\Pagination\Paginator || $paginator instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
    <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <form method="GET" action="{{ $action }}" class="flex items-center gap-2">
            @foreach($query as $key => $value)
                @if($key !== $pageParam && $key !== $perPageParam)
                    @php($renderHidden($key, $value))
                @endif
            @endforeach

            <label class="text-sm text-gray-600">Rows per page</label>
            <select name="{{ $perPageParam }}" class="block rounded-md border-gray-300 py-1.5 pl-2 pr-8 text-sm {{ $theme['focus'] }}" onchange="this.form.submit()">
                @foreach($perPageOptions as $option)
                    <option value="{{ $option }}" @selected((int) $selectedPerPage === (int) $option)>{{ $option }}</option>
                @endforeach
            </select>
        </form>

        <div class="flex w-full items-center justify-between gap-3 sm:w-auto">
            <div class="text-sm text-gray-600">
                @if(method_exists($paginator, 'total'))
                    <span>Showing {{ $paginator->firstItem() ?? 0 }}–{{ $paginator->lastItem() ?? 0 }} of {{ $paginator->total() }}</span>
                @else
                    <span>Showing page {{ $paginator->currentPage() }}</span>
                @endif
            </div>
            <div class="inline-flex shadow-sm rounded-md" role="group">
                @php($prevDisabled = $paginator->onFirstPage())
                <a href="{{ $prevDisabled ? '#' : $paginator->previousPageUrl() }}" class="px-3 py-1.5 text-sm font-medium bg-white border {{ $theme['border'] }} rounded-l-md {{ $theme['hover'] }} focus:z-10 {{ $prevDisabled ? 'opacity-50 cursor-not-allowed pointer-events-none' : '' }}">Previous</a>
                <span class="px-3 py-1.5 text-sm font-medium bg-white border-t border-b {{ $theme['border'] }}">Page {{ $paginator->currentPage() }}@if(method_exists($paginator, 'lastPage')) / {{ $paginator->lastPage() }} @endif</span>
                @php($nextDisabled = ! $paginator->hasMorePages())
                <a href="{{ $nextDisabled ? '#' : $paginator->nextPageUrl() }}" class="px-3 py-1.5 text-sm font-medium bg-white border {{ $theme['border'] }} rounded-r-md {{ $theme['hover'] }} focus:z-10 {{ $nextDisabled ? 'opacity-50 cursor-not-allowed pointer-events-none' : '' }}">Next</a>
            </div>
        </div>
    </div>
@endif