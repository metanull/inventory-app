@props([
    'title' => '',
    'subtitle' => '',
    'fields' => [],
    'actions' => null,
    'entity' => null,
])

@php($c = $entityColor($entity))

<div class="border-b border-gray-200 p-4 hover:bg-gray-50">
    <div class="flex items-start justify-between">
        <div class="flex-1 min-w-0">
            @if($title)
                <h3 class="text-sm font-medium text-gray-900 truncate">{{ $title }}</h3>
            @endif
            @if($subtitle)
                <p class="text-xs text-gray-500 mt-1">{{ $subtitle }}</p>
            @endif
            
            <!-- Fields in mobile-friendly layout -->
            @if(count($fields) > 0)
                <div class="mt-3 space-y-2">
                    @foreach($fields as $label => $value)
                        <div class="flex justify-between text-xs">
                            <span class="text-gray-500 font-medium">{{ $label }}:</span>
                            <span class="text-gray-700 ml-2 text-right">{{ $value }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
        
        @if($actions)
            <div class="ml-4 flex-shrink-0">
                {{ $actions }}
            </div>
        @endif
    </div>
</div>