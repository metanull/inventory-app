<x-filament-panels::page>
    @php $roots = $this->getRoots(); @endphp
    <div class="space-y-4">
        @if ($this->getRootCount() > $roots->count())
            <div class="fi-ta-header-cell text-sm text-gray-500 dark:text-gray-400 px-1">
                Showing the first {{ $roots->count() }} of {{ $this->getRootCount() }} root items. Use the search to narrow results.
            </div>
        @endif
        <div class="fi-ta-content overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900">
            <div class="divide-y divide-gray-200 dark:divide-white/10">
                @foreach ($roots as $root)
                    @include('filament.pages.partials.item-tree-node', [
                        'node' => $root,
                        'depth' => 0,
                    ])
                @endforeach

                @if ($roots->isEmpty())
                    <div class="px-6 py-12 text-center">
                        <x-filament::icon
                            icon="heroicon-o-cube"
                            class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500"
                        />
                        <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">
                            No items found.
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-filament-panels::page>
