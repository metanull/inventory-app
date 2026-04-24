<x-filament-panels::page>
    <div class="space-y-4">
        <div class="fi-ta-content overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900">
            <div class="divide-y divide-gray-200 dark:divide-white/10">
                @foreach ($this->getRoots() as $root)
                    @include('filament.pages.partials.collection-tree-node', [
                        'node' => $root,
                        'depth' => 0,
                    ])
                @endforeach

                @if ($this->getRoots()->isEmpty())
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
    </div>
</x-filament-panels::page>
