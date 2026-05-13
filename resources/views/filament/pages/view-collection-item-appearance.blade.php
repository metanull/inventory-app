<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">Appearance identity</x-slot>

        <dl class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-2">
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Collection</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                    @if ($this->collectionUrl())
                        <a href="{{ $this->collectionUrl() }}" class="text-primary-600 hover:underline dark:text-primary-400">
                            {{ $collection->display_label ?? $collection->internal_name }}
                        </a>
                    @else
                        {{ $collection->display_label ?? $collection->internal_name }}
                    @endif
                </dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Item</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                    @if ($this->itemUrl())
                        <a href="{{ $this->itemUrl() }}" class="text-primary-600 hover:underline dark:text-primary-400">
                            {{ $item->display_label ?? $item->internal_name }}
                        </a>
                    @else
                        {{ $item->display_label ?? $item->internal_name }}
                    @endif
                </dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Display order</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                    {{ $displayOrder ?? '—' }}
                </dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Languages with contextual text</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                    @if ($contextualDescriptions !== [])
                        {{ implode(', ', array_keys($contextualDescriptions)) }}
                    @else
                        <span class="text-gray-400">—</span>
                    @endif
                </dd>
            </div>
        </dl>
    </x-filament::section>

    @if ($this->allLanguageIds() !== [])
        @foreach ($this->allLanguageIds() as $langId)
            <x-filament::section>
                <x-slot name="heading">{{ strtoupper($langId) }}</x-slot>

                <dl class="grid grid-cols-1 gap-x-4 gap-y-4">
                    @if (isset($contextualDescriptions[$langId]) && $contextualDescriptions[$langId] !== '')
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Contextual description</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 whitespace-pre-wrap">{{ $contextualDescriptions[$langId] }}</dd>
                        </div>
                    @endif
                    @if (isset($sourceBcByLanguage[$langId]) && $sourceBcByLanguage[$langId] !== '')
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Source (legacy reference)</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 font-mono">{{ $sourceBcByLanguage[$langId] }}</dd>
                        </div>
                    @endif
                </dl>
            </x-filament::section>
        @endforeach
    @else
        <x-filament::section>
            <p class="text-sm text-gray-500 dark:text-gray-400">No contextual text available for this appearance.</p>
        </x-filament::section>
    @endif
</x-filament-panels::page>
