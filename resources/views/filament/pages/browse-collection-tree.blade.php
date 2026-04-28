<x-filament-panels::page>
    @php
        $roots = $this->getRoots();
        $rootCount = $this->getRootCount();
        $totalPages = $this->getTotalPages();
    @endphp
    <div class="space-y-4">
        {{-- Search bar --}}
        <div class="flex gap-3">
            <div class="flex-1">
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search by name or legacy code…"
                    class="fi-input block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm dark:border-white/10 dark:bg-gray-800 dark:text-white"
                />
            </div>
        </div>

        {{-- Count / subset messaging --}}
        @if ($search !== '')
            <div class="px-1 text-sm text-gray-500 dark:text-gray-400">
                {{ $rootCount }} {{ Str::plural('result', $rootCount) }} for &ldquo;{{ $search }}&rdquo;
                @if ($totalPages > 1)
                    &mdash; page {{ $page }} of {{ $totalPages }}
                @endif
            </div>
        @elseif ($rootCount > $roots->count())
            <div class="px-1 text-sm text-gray-500 dark:text-gray-400">
                Showing {{ $roots->count() }} of {{ $rootCount }} root collections &mdash; page {{ $page }} of {{ $totalPages }}.
            </div>
        @else
            <div class="px-1 text-sm text-gray-500 dark:text-gray-400">
                {{ $rootCount }} {{ Str::plural('root collection', $rootCount) }}
            </div>
        @endif

        <div class="fi-ta-content overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900">
            <div class="divide-y divide-gray-200 dark:divide-white/10">
                @foreach ($roots as $root)
                    @include('filament.pages.partials.collection-tree-node', [
                        'node' => $root,
                        'depth' => 0,
                    ])
                @endforeach

                @if ($roots->isEmpty())
                    <div class="px-6 py-12 text-center">
                        <x-filament::icon
                            icon="heroicon-o-archive-box"
                            class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500"
                        />
                        <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">
                            No collections found.
                        </p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Pagination controls --}}
        @if ($totalPages > 1)
            <div class="flex items-center justify-between px-1">
                <button
                    wire:click="previousPage"
                    @disabled($page <= 1)
                    class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-sm text-gray-700 shadow-sm hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50 dark:border-white/10 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/5"
                >
                    &larr; Previous
                </button>
                <span class="text-sm text-gray-500 dark:text-gray-400">
                    Page {{ $page }} of {{ $totalPages }}
                </span>
                <button
                    wire:click="nextPage"
                    @disabled($page >= $totalPages)
                    class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-sm text-gray-700 shadow-sm hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50 dark:border-white/10 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/5"
                >
                    Next &rarr;
                </button>
            </div>
        @endif
    </div>
</x-filament-panels::page>
