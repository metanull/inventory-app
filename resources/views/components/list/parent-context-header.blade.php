@props([
    'breadcrumbs' => [],
    'title' => null,
    'description' => null,
    'parentLabel' => null,
    'parentValue' => null,
    'parentUrl' => null,
    'backUrl' => null,
    'backLabel' => 'Back',
])

<div class="mb-6 rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
    @if($breadcrumbs !== [])
        <nav class="mb-3 flex flex-wrap items-center gap-1 text-sm text-gray-500" aria-label="Breadcrumb">
            @foreach($breadcrumbs as $crumb)
                @if(! $loop->first)
                    <x-heroicon-o-chevron-right class="h-4 w-4 shrink-0" />
                @endif

                @if(! empty($crumb['url']))
                    <a href="{{ $crumb['url'] }}" class="hover:text-gray-700">
                        {{ $crumb['label'] }}
                    </a>
                @else
                    <span class="font-medium text-gray-900">{{ $crumb['label'] }}</span>
                @endif
            @endforeach
        </nav>
    @endif

    <div class="flex flex-wrap items-start justify-between gap-4">
        <div class="space-y-2">
            @if($title)
                <h2 class="text-lg font-semibold text-gray-900">{{ $title }}</h2>
            @endif

            @if($description)
                <p class="text-sm text-gray-600">{{ $description }}</p>
            @endif

            @if($parentValue)
                <div class="flex flex-wrap items-center gap-2 text-sm text-gray-600">
                    @if($parentLabel)
                        <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold uppercase tracking-wide text-gray-600">
                            {{ $parentLabel }}
                        </span>
                    @endif

                    @if($parentUrl)
                        <a href="{{ $parentUrl }}" class="font-medium text-gray-900 hover:text-gray-700 hover:underline">
                            {{ $parentValue }}
                        </a>
                    @else
                        <span class="font-medium text-gray-900">{{ $parentValue }}</span>
                    @endif
                </div>
            @endif
        </div>

        @if($backUrl)
            <x-ui.button :href="$backUrl" variant="secondary" size="sm">
                {{ $backLabel }}
            </x-ui.button>
        @endif
    </div>
</div>